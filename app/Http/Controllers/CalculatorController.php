<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CalculatorController extends APIController
{
    public function __construct()
    {
        // All calculator endpoints are public (no authentication required for testing)
        // Can add authentication later when implementing subscription lock
    }

    /**
     * Business Calculators
     */

    /**
     * Calculate Break-Even Point
     */
    public function breakEven(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fixed_costs' => 'required|numeric|min:0',
            'variable_cost_per_unit' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $fixedCosts = $request->fixed_costs;
        $variableCost = $request->variable_cost_per_unit;
        $price = $request->price_per_unit;

        if ($price <= $variableCost) {
            return $this->sendError('Price must be greater than variable cost per unit', [], 400);
        }

        $units = $fixedCosts / ($price - $variableCost);
        $revenue = $units * $price;

        return $this->sendResponse([
            'units' => round($units, 2),
            'revenue' => round($revenue, 2)
        ], 'Break-even calculated successfully');
    }

    /**
     * Calculate Return on Equity (DuPont Model)
     */
    public function roe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'net_income' => 'required|numeric',
            'total_assets' => 'required|numeric|min:0',
            'total_equity' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $netIncome = $request->net_income;
        $totalAssets = $request->total_assets;
        $totalEquity = $request->total_equity;
        $revenue = $request->revenue;

        if ($totalEquity == 0 || $revenue == 0) {
            return $this->sendError('Total equity and revenue must be greater than 0', [], 400);
        }

        $profitMargin = ($netIncome / $revenue) * 100;
        $assetTurnover = $revenue / $totalAssets;
        $financialLeverage = $totalAssets / $totalEquity;
        $roe = $profitMargin * $assetTurnover * $financialLeverage;

        return $this->sendResponse([
            'profit_margin' => round($profitMargin, 2),
            'asset_turnover' => round($assetTurnover, 2),
            'financial_leverage' => round($financialLeverage, 2),
            'roe' => round($roe, 2)
        ], 'ROE calculated successfully');
    }

    /**
     * Calculate Operating Profit Margin
     */
    public function operatingMargin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operating_income' => 'required|numeric',
            'revenue' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $operatingIncome = $request->operating_income;
        $revenue = $request->revenue;

        if ($revenue == 0) {
            return $this->sendError('Revenue must be greater than 0', [], 400);
        }

        $margin = ($operatingIncome / $revenue) * 100;

        return $this->sendResponse([
            'margin' => round($margin, 2)
        ], 'Operating margin calculated successfully');
    }

    /**
     * Calculate Gross Profit Margin
     */
    public function grossMargin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'revenue' => 'required|numeric|min:0',
            'cost_of_goods_sold' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $revenue = $request->revenue;
        $cogs = $request->cost_of_goods_sold;

        if ($revenue == 0) {
            return $this->sendError('Revenue must be greater than 0', [], 400);
        }

        $grossProfit = $revenue - $cogs;
        $margin = ($grossProfit / $revenue) * 100;
        $markup = $cogs > 0 ? ($grossProfit / $cogs) * 100 : 0;

        return $this->sendResponse([
            'gross_profit' => round($grossProfit, 2),
            'margin' => round($margin, 2),
            'markup' => round($markup, 2)
        ], 'Gross margin calculated successfully');
    }

    /**
     * Calculate Business Valuation (DCF)
     */
    public function businessValuation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'free_cash_flow' => 'required|numeric|min:0',
            'growth_rate' => 'required|numeric',
            'discount_rate' => 'required|numeric',
            'terminal_growth_rate' => 'required|numeric',
            'years' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $fcf = $request->free_cash_flow;
        $growthRate = $request->growth_rate / 100;
        $discountRate = $request->discount_rate / 100;
        $terminalGrowthRate = $request->terminal_growth_rate / 100;
        $years = $request->years;

        if ($discountRate <= $growthRate || $discountRate <= $terminalGrowthRate) {
            return $this->sendError('Discount rate must be greater than growth and terminal growth rates', [], 400);
        }

        $presentValue = 0;
        $projectedFCF = $fcf;

        for ($i = 1; $i <= $years; $i++) {
            $projectedFCF *= (1 + $growthRate);
            $presentValue += $projectedFCF / pow(1 + $discountRate, $i);
        }

        $terminalValue = ($projectedFCF * (1 + $terminalGrowthRate)) / ($discountRate - $terminalGrowthRate);
        $terminalValuePV = $terminalValue / pow(1 + $discountRate, $years);
        $totalValue = $presentValue + $terminalValuePV;

        return $this->sendResponse([
            'present_value' => round($presentValue, 2),
            'terminal_value' => round($terminalValue, 2),
            'terminal_value_pv' => round($terminalValuePV, 2),
            'total_value' => round($totalValue, 2)
        ], 'Business valuation calculated successfully');
    }

    /**
     * Calculate VAT
     */
    public function vat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'calculation_type' => 'required|in:add,remove',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $amount = $request->amount;
        $rate = $request->vat_rate / 100;
        $type = $request->calculation_type;

        if ($type === 'add') {
            $vatAmount = $amount * $rate;
            $total = $amount + $vatAmount;
            return $this->sendResponse([
                'net_amount' => round($amount, 2),
                'vat_amount' => round($vatAmount, 2),
                'total' => round($total, 2)
            ], 'VAT added successfully');
        } else {
            $netAmount = $amount / (1 + $rate);
            $vatAmount = $amount - $netAmount;
            return $this->sendResponse([
                'gross_amount' => round($amount, 2),
                'vat_amount' => round($vatAmount, 2),
                'net_amount' => round($netAmount, 2)
            ], 'VAT removed successfully');
        }
    }

    /**
     * Calculate Free Cash Flow to Firm (FCFF)
     */
    public function fcff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ebit' => 'required|numeric',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'depreciation' => 'required|numeric',
            'capital_expenditure' => 'required|numeric',
            'change_in_working_capital' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $ebit = $request->ebit;
        $taxRate = $request->tax_rate / 100;
        $depreciation = $request->depreciation;
        $capex = $request->capital_expenditure;
        $changeWC = $request->change_in_working_capital;

        $nopat = $ebit * (1 - $taxRate);
        $fcffValue = $nopat + $depreciation - $capex - $changeWC;

        return $this->sendResponse([
            'nopat' => round($nopat, 2),
            'fcff' => round($fcffValue, 2)
        ], 'FCFF calculated successfully');
    }

    /**
     * Real Estate Calculators
     */

    /**
     * Calculate Mortgage Payment
     */
    public function mortgage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'principal' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|in:15,20,30',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $principal = $request->principal - $request->down_payment;
        $annualRate = $request->interest_rate / 100;
        $monthlyRate = $annualRate / 12;
        $numberOfPayments = $request->loan_term * 12;

        if ($principal <= 0 || $annualRate <= 0) {
            return $this->sendError('Principal after down payment and interest rate must be greater than 0', [], 400);
        }

        $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments)) / (pow(1 + $monthlyRate, $numberOfPayments) - 1);
        $totalPayment = $monthlyPayment * $numberOfPayments;
        $totalInterest = $totalPayment - $principal;

        return $this->sendResponse([
            'monthly_payment' => round($monthlyPayment, 2),
            'total_payment' => round($totalPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'principal' => round($principal, 2)
        ], 'Mortgage calculated successfully');
    }

    /**
     * Calculate Affordability
     */
    public function affordability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annual_income' => 'required|numeric|min:0',
            'monthly_debt' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|in:15,20,30',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $annualIncome = $request->annual_income;
        $monthlyDebt = $request->monthly_debt;
        $downPayment = $request->down_payment;
        $annualRate = $request->interest_rate / 100;
        $monthlyRate = $annualRate / 12;
        $numberOfPayments = $request->loan_term * 12;

        $monthlyIncome = $annualIncome / 12;
        $maxDTI = 0.36;
        $maxMonthlyPayment = ($monthlyIncome * $maxDTI) - $monthlyDebt;

        if ($maxMonthlyPayment <= 0) {
            return $this->sendError('Monthly debt payments exceed maximum allowable (36% of income)', [], 400);
        }

        $maxLoanAmount = $maxMonthlyPayment * (pow(1 + $monthlyRate, $numberOfPayments) - 1) / ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments));
        $maxHomePrice = $maxLoanAmount + $downPayment;

        return $this->sendResponse([
            'max_monthly_payment' => round($maxMonthlyPayment, 2),
            'max_loan_amount' => round($maxLoanAmount, 2),
            'max_home_price' => round($maxHomePrice, 2)
        ], 'Affordability calculated successfully');
    }

    /**
     * Calculate ROI
     */
    public function roi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_price' => 'required|numeric|min:0',
            'monthly_rent' => 'required|numeric|min:0',
            'expenses' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $purchasePrice = $request->purchase_price;
        $monthlyRent = $request->monthly_rent;
        $annualExpenses = $request->expenses * 12;
        $downPayment = $request->down_payment;

        if ($downPayment == 0 || $purchasePrice == 0) {
            return $this->sendError('Down payment and purchase price must be greater than 0', [], 400);
        }

        $annualRent = $monthlyRent * 12;
        $annualCashFlow = $annualRent - $annualExpenses;
        $cashOnCashROI = ($annualCashFlow / $downPayment) * 100;
        $capRate = ($annualCashFlow / $purchasePrice) * 100;

        return $this->sendResponse([
            'annual_rent' => round($annualRent, 2),
            'annual_cash_flow' => round($annualCashFlow, 2),
            'cash_on_cash_roi' => round($cashOnCashROI, 2),
            'cap_rate' => round($capRate, 2)
        ], 'ROI calculated successfully');
    }

    /**
     * Calculate Rent vs Buy
     */
    public function rentVsBuy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_price' => 'required|numeric|min:0',
            'monthly_rent' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'property_tax_rate' => 'required|numeric|min:0',
            'appreciation_rate' => 'required|numeric',
            'rent_increase_rate' => 'required|numeric',
            'years' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $homePrice = $request->home_price;
        $monthlyRent = $request->monthly_rent;
        $downPayment = $request->down_payment;
        $interestRate = $request->interest_rate / 100;
        $propertyTaxRate = $request->property_tax_rate / 100;
        $appreciationRate = $request->appreciation_rate / 100;
        $rentIncreaseRate = $request->rent_increase_rate / 100;
        $years = $request->years;

        $loanAmount = $homePrice - $downPayment;
        $monthlyRate = $interestRate / 12;
        $numberOfPayments = $years * 12;

        $monthlyMortgage = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments)) / (pow(1 + $monthlyRate, $numberOfPayments) - 1);
        $monthlyPropertyTax = ($homePrice * $propertyTaxRate) / 12;
        $monthlyBuyingCost = $monthlyMortgage + $monthlyPropertyTax;

        $totalRentCost = 0;
        $totalBuyCost = $downPayment;
        $currentRent = $monthlyRent;
        $currentHomeValue = $homePrice;

        for ($i = 0; $i < $years; $i++) {
            $totalRentCost += $currentRent * 12;
            $totalBuyCost += ($monthlyMortgage + $monthlyPropertyTax) * 12;
            $currentRent *= (1 + $rentIncreaseRate);
            $currentHomeValue *= (1 + $appreciationRate);
        }

        $homeEquity = $currentHomeValue - ($loanAmount - $downPayment);
        $netBuyCost = $totalBuyCost - $homeEquity;

        return $this->sendResponse([
            'monthly_buying_cost' => round($monthlyBuyingCost, 2),
            'total_rent_cost' => round($totalRentCost, 2),
            'total_buy_cost' => round($totalBuyCost, 2),
            'home_equity' => round($homeEquity, 2),
            'net_buy_cost' => round($netBuyCost, 2),
            'recommendation' => $totalRentCost < $netBuyCost ? 'Renting is better' : 'Buying is better'
        ], 'Rent vs Buy calculated successfully');
    }

    /**
     * Calculate Property Tax
     */
    public function propertyTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_value' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $propertyValue = $request->property_value;
        $taxRate = $request->tax_rate / 100;

        $annualTax = $propertyValue * $taxRate;
        $monthlyTax = $annualTax / 12;

        return $this->sendResponse([
            'annual_tax' => round($annualTax, 2),
            'monthly_tax' => round($monthlyTax, 2)
        ], 'Property tax calculated successfully');
    }

    /**
     * Calculate Closing Costs
     */
    public function closingCosts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_price' => 'required|numeric|min:0',
            'loan_amount' => 'required|numeric|min:0',
            'origination_fee' => 'required|numeric|min:0|max:10',
            'title_insurance' => 'required|numeric|min:0|max:10',
            'appraisal_fee' => 'required|numeric|min:0',
            'attorney_fee' => 'required|numeric|min:0',
            'other_fees' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $homePrice = $request->home_price;
        $loanAmount = $request->loan_amount;
        $originationFee = $request->origination_fee / 100;
        $titleInsurance = $request->title_insurance / 100;
        $appraisalFee = $request->appraisal_fee;
        $attorneyFee = $request->attorney_fee;
        $otherFees = $request->other_fees;

        $originationFeeAmount = $loanAmount * $originationFee;
        $titleInsuranceAmount = $homePrice * $titleInsurance;
        $totalClosingCosts = $originationFeeAmount + $titleInsuranceAmount + $appraisalFee + $attorneyFee + $otherFees;

        return $this->sendResponse([
            'origination_fee' => round($originationFeeAmount, 2),
            'title_insurance' => round($titleInsuranceAmount, 2),
            'appraisal_fee' => round($appraisalFee, 2),
            'attorney_fee' => round($attorneyFee, 2),
            'other_fees' => round($otherFees, 2),
            'total_closing_costs' => round($totalClosingCosts, 2),
            'percentage_of_price' => round(($totalClosingCosts / $homePrice) * 100, 2)
        ], 'Closing costs calculated successfully');
    }

    /**
     * Vehicle Calculators
     */

    /**
     * Calculate Auto Loan
     */
    public function autoLoan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|in:36,48,60,72',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $principal = $request->vehicle_price - $request->down_payment;
        $annualRate = $request->interest_rate / 100;
        $monthlyRate = $annualRate / 12;
        $numberOfPayments = $request->loan_term;

        if ($principal <= 0 || $annualRate <= 0) {
            return $this->sendError('Principal after down payment and interest rate must be greater than 0', [], 400);
        }

        $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments)) / (pow(1 + $monthlyRate, $numberOfPayments) - 1);
        $totalPayment = $monthlyPayment * $numberOfPayments;
        $totalInterest = $totalPayment - $principal;

        return $this->sendResponse([
            'monthly_payment' => round($monthlyPayment, 2),
            'total_payment' => round($totalPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'principal' => round($principal, 2)
        ], 'Auto loan calculated successfully');
    }

    /**
     * Calculate Lease vs Buy
     */
    public function leaseVsBuy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'residual_value' => 'required|numeric|min:0|max:100',
            'lease_term' => 'required|integer|in:24,36,48',
            'money_factor' => 'required|numeric|min:0',
            'loan_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|in:36,48,60,72',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $vehiclePrice = $request->vehicle_price;
        $downPayment = $request->down_payment;
        $residualValuePercent = $request->residual_value / 100;
        $leaseTerm = $request->lease_term;
        $moneyFactor = $request->money_factor;
        $loanInterestRate = $request->loan_interest_rate / 100;
        $loanTerm = $request->loan_term;

        $residualValue = $vehiclePrice * $residualValuePercent;
        $depreciation = ($vehiclePrice - $residualValue) / $leaseTerm;
        $rentCharge = ($vehiclePrice + $residualValue) * $moneyFactor;
        $leasePayment = $depreciation + $rentCharge;
        $totalLeaseCost = ($leasePayment * $leaseTerm) + $downPayment;

        $loanAmount = $vehiclePrice - $downPayment;
        $monthlyRate = $loanInterestRate / 12;
        $monthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $loanTerm)) / (pow(1 + $monthlyRate, $loanTerm) - 1);
        $totalBuyCost = ($monthlyPayment * $loanTerm) + $downPayment;

        return $this->sendResponse([
            'lease_payment' => round($leasePayment, 2),
            'total_lease_cost' => round($totalLeaseCost, 2),
            'buy_payment' => round($monthlyPayment, 2),
            'total_buy_cost' => round($totalBuyCost, 2),
            'monthly_difference' => round($monthlyPayment - $leasePayment, 2),
            'recommendation' => $totalLeaseCost < $totalBuyCost ? 'Leasing is better' : 'Buying is better'
        ], 'Lease vs Buy calculated successfully');
    }

    /**
     * Calculate Depreciation
     */
    public function depreciation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_price' => 'required|numeric|min:0',
            'years' => 'required|integer|min:1|max:10',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $purchasePrice = $request->purchase_price;
        $years = $request->years;
        $depreciationRate = $request->depreciation_rate / 100;

        $currentValue = $purchasePrice;
        $yearlyValues = [];

        for ($i = 1; $i <= $years; $i++) {
            $currentValue = $currentValue * (1 - $depreciationRate);
            $yearlyValues[] = [
                'year' => $i,
                'value' => round($currentValue, 2),
                'depreciated' => round($purchasePrice - $currentValue, 2)
            ];
        }

        return $this->sendResponse([
            'yearly_values' => $yearlyValues,
            'final_value' => round($currentValue, 2),
            'total_depreciation' => round($purchasePrice - $currentValue, 2)
        ], 'Depreciation calculated successfully');
    }

    /**
     * Calculate Fuel Cost
     */
    public function fuelCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distance' => 'required|numeric|min:0',
            'mpg' => 'required|numeric|min:0',
            'fuel_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $distance = $request->distance;
        $mpg = $request->mpg;
        $fuelPrice = $request->fuel_price;

        if ($mpg == 0) {
            return $this->sendError('MPG must be greater than 0', [], 400);
        }

        $gallonsNeeded = $distance / $mpg;
        $totalCost = $gallonsNeeded * $fuelPrice;
        $costPerMile = $totalCost / $distance;

        return $this->sendResponse([
            'gallons_needed' => round($gallonsNeeded, 2),
            'total_cost' => round($totalCost, 2),
            'cost_per_mile' => round($costPerMile, 3)
        ], 'Fuel cost calculated successfully');
    }

    /**
     * Estimate Insurance
     */
    public function insurance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_value' => 'required|numeric|min:0',
            'driver_age' => 'required|integer|min:16|max:100',
            'driving_record' => 'required|in:clean,minor,major',
            'coverage_level' => 'required|in:liability,full,premium',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $vehicleValue = $request->vehicle_value;
        $driverAge = $request->driver_age;
        $drivingRecord = $request->driving_record;
        $coverageLevel = $request->coverage_level;

        $baseRate = $vehicleValue * 0.03;

        if ($driverAge < 25) {
            $baseRate *= 1.5;
        } elseif ($driverAge >= 65) {
            $baseRate *= 1.2;
        }

        if ($drivingRecord === 'clean') {
            $baseRate *= 1;
        } elseif ($drivingRecord === 'minor') {
            $baseRate *= 1.3;
        } elseif ($drivingRecord === 'major') {
            $baseRate *= 1.8;
        }

        if ($coverageLevel === 'liability') {
            $baseRate *= 0.6;
        } elseif ($coverageLevel === 'full') {
            $baseRate *= 1;
        } elseif ($coverageLevel === 'premium') {
            $baseRate *= 1.4;
        }

        $monthlyPremium = $baseRate / 12;
        $annualPremium = $baseRate;

        return $this->sendResponse([
            'monthly_premium' => round($monthlyPremium, 2),
            'annual_premium' => round($annualPremium, 2)
        ], 'Insurance estimated successfully');
    }

    /**
     * Calculate Total Cost of Ownership
     */
    public function tco(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'loan_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|in:36,48,60,72',
            'annual_miles' => 'required|numeric|min:0',
            'mpg' => 'required|numeric|min:0',
            'fuel_price' => 'required|numeric|min:0',
            'insurance_monthly' => 'required|numeric|min:0',
            'maintenance_annual' => 'required|numeric|min:0',
            'ownership_years' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        $purchasePrice = $request->purchase_price;
        $downPayment = $request->down_payment;
        $loanInterestRate = $request->loan_interest_rate / 100;
        $loanTerm = $request->loan_term;
        $annualMiles = $request->annual_miles;
        $mpg = $request->mpg;
        $fuelPrice = $request->fuel_price;
        $insuranceMonthly = $request->insurance_monthly;
        $maintenanceAnnual = $request->maintenance_annual;
        $ownershipYears = $request->ownership_years;

        if ($mpg == 0) {
            return $this->sendError('MPG must be greater than 0', [], 400);
        }

        $loanAmount = $purchasePrice - $downPayment;
        $monthlyRate = $loanInterestRate / 12;
        $monthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $loanTerm)) / (pow(1 + $monthlyRate, $loanTerm) - 1);
        $totalLoanCost = $monthlyPayment * $loanTerm;

        $totalMiles = $annualMiles * $ownershipYears;
        $totalGallons = $totalMiles / $mpg;
        $totalFuelCost = $totalGallons * $fuelPrice;

        $totalInsuranceCost = $insuranceMonthly * 12 * $ownershipYears;
        $totalMaintenanceCost = $maintenanceAnnual * $ownershipYears;

        $depreciation = 0;
        $currentValue = $purchasePrice;
        for ($i = 0; $i < $ownershipYears; $i++) {
            $yearlyDep = $currentValue * 0.15;
            $depreciation += $yearlyDep;
            $currentValue -= $yearlyDep;
        }

        $totalCost = $downPayment + $totalLoanCost + $totalFuelCost + $totalInsuranceCost + $totalMaintenanceCost + $depreciation;
        $monthlyAverage = $totalCost / ($ownershipYears * 12);
        $costPerMile = $totalCost / $totalMiles;

        return $this->sendResponse([
            'down_payment' => round($downPayment, 2),
            'total_loan_cost' => round($totalLoanCost, 2),
            'total_fuel_cost' => round($totalFuelCost, 2),
            'total_insurance_cost' => round($totalInsuranceCost, 2),
            'total_maintenance_cost' => round($totalMaintenanceCost, 2),
            'depreciation' => round($depreciation, 2),
            'total_cost' => round($totalCost, 2),
            'monthly_average' => round($monthlyAverage, 2),
            'cost_per_mile' => round($costPerMile, 2)
        ], 'TCO calculated successfully');
    }
}
