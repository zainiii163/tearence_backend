@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div id="projectDetail">
        <!-- Loading state -->
        <div class="text-center py-12">
            <p class="text-gray-500">Loading project...</p>
        </div>
    </div>
</div>

<script>
const projectId = new URL(window.location).pathname.split('/').pop();

async function loadProjectDetail() {
    try {
        const response = await fetch(`/api/v1/funding/${projectId}`);
        const data = await response.json();

        if (data.success) {
            displayProjectDetail(data.data);
        } else {
            showError('Project not found');
        }
    } catch (error) {
        showError('Error loading project: ' + error.message);
    }
}

function displayProjectDetail(project) {
    const fundingPercent = Math.round((project.amount_raised / project.funding_goal) * 100);
    const daysRemaining = project.days_remaining || 0;

    document.getElementById('projectDetail').innerHTML = `
        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2">
                <!-- Cover Image -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <img src="${project.cover_image}" alt="${project.title}" class="w-full h-96 object-cover">
                </div>

                <!-- Project Info -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h1 class="text-4xl font-bold mb-2">${project.title}</h1>
                    <p class="text-lg text-gray-600 mb-4">${project.tagline || ''}</p>

                    <div class="flex items-center gap-4 mb-6 pb-6 border-b">
                        <div>
                            <p class="text-sm text-gray-500">Created by</p>
                            <p class="font-bold">${project.user.name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Category</p>
                            <p class="font-bold">${project.category}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-bold">${project.country}</p>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-6">
                        <div class="flex gap-4 border-b mb-4">
                            <button class="tab-button active px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-bold" data-tab="description">Overview</button>
                            <button class="tab-button px-4 py-2 text-gray-600 font-bold" data-tab="rewards">Rewards</button>
                            <button class="tab-button px-4 py-2 text-gray-600 font-bold" data-tab="backers">Backers</button>
                        </div>

                        <!-- Description Tab -->
                        <div id="description-tab" class="tab-content">
                            <h2 class="text-2xl font-bold mb-4">About This Project</h2>
                            <div class="prose max-w-none mb-6">${project.description}</div>

                            ${project.problem_solving ? `
                                <h3 class="text-xl font-bold mb-3">The Problem</h3>
                                <div class="prose max-w-none mb-6">${project.problem_solving}</div>
                            ` : ''}

                            ${project.vision_mission ? `
                                <h3 class="text-xl font-bold mb-3">Our Vision</h3>
                                <div class="prose max-w-none mb-6">${project.vision_mission}</div>
                            ` : ''}

                            ${project.team_members ? `
                                <h3 class="text-xl font-bold mb-4">Team</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    ${project.team_members.map(member => `
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <p class="font-bold">${member.name}</p>
                                            <p class="text-sm text-gray-600">${member.role}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>

                        <!-- Rewards Tab -->
                        <div id="rewards-tab" class="tab-content hidden">
                            <h2 class="text-2xl font-bold mb-4">Reward Tiers</h2>
                            ${project.rewards && project.rewards.length > 0 ? `
                                <div class="space-y-4">
                                    ${project.rewards.map(reward => `
                                        <div class="border border-gray-300 rounded-lg p-4">
                                            <h3 class="text-lg font-bold mb-2">${reward.title}</h3>
                                            <p class="text-gray-600 mb-3">${reward.description}</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-lg font-bold text-blue-600">${project.currency} ${parseFloat(reward.minimum_contribution).toLocaleString()}</span>
                                                ${reward.limit ? `<span class="text-sm text-gray-500">${reward.claimed_count}/${reward.limit} claimed</span>` : ''}
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : `<p class="text-gray-500">No rewards available for this project</p>`}
                        </div>

                        <!-- Backers Tab -->
                        <div id="backers-tab" class="tab-content hidden">
                            <h2 class="text-2xl font-bold mb-4">Recent Backers</h2>
                            ${project.pledges && project.pledges.length > 0 ? `
                                <div class="space-y-3">
                                    ${project.pledges.map(pledge => `
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <p class="font-bold">${pledge.is_anonymous ? 'Anonymous' : pledge.user.name}</p>
                                            <p class="text-sm text-gray-600">Backed ${project.currency} ${parseFloat(pledge.amount).toLocaleString()}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : `<p class="text-gray-500">No backers yet</p>`}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-span-1">
                <!-- Funding Box -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6 sticky top-4">
                    <div class="mb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-2xl font-bold">${project.currency} ${parseFloat(project.amount_raised).toLocaleString()}</span>
                            <span class="text-lg text-gray-600">of ${project.currency} ${parseFloat(project.funding_goal).toLocaleString()}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full" style="width: ${Math.min(fundingPercent, 100)}%"></div>
                        </div>
                        <p class="mt-2 font-bold text-lg">${fundingPercent}% Funded</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 py-4 border-y">
                        <div>
                            <p class="text-sm text-gray-600">Backers</p>
                            <p class="text-2xl font-bold">${project.backer_count}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Days Left</p>
                            <p class="text-2xl font-bold">${daysRemaining}</p>
                        </div>
                    </div>

                    <button onclick="pledgeProject()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg mb-3 transition">
                        Back This Project
                    </button>

                    <button onclick="saveFunding()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 rounded-lg transition">
                        ♡ Save
                    </button>
                </div>

                <!-- Project Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="font-bold mb-4">Project Info</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-600">Funding Type</p>
                            <p class="font-bold">${project.funding_model}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Views</p>
                            <p class="font-bold">${project.views_count}</p>
                        </div>
                        ${project.website ? `
                            <div>
                                <p class="text-gray-600">Website</p>
                                <a href="${project.website}" target="_blank" class="font-bold text-blue-600 hover:underline">Visit</a>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add tab event listeners
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;

            // Update active button
            document.querySelectorAll('.tab-button').forEach(b => {
                b.classList.remove('active', 'border-b-2', 'border-blue-600', 'text-blue-600');
                b.classList.add('text-gray-600');
            });
            this.classList.add('active', 'border-b-2', 'border-blue-600', 'text-blue-600');
            this.classList.remove('text-gray-600');

            // Update visible tab
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(`${tabName}-tab`).classList.remove('hidden');
        });
    });
}

function pledgeProject() {
    const amount = prompt('Enter pledge amount (minimum ' + new URL(document.location).searchParams.get('min_contribution', 1) + '):');
    if (!amount) return;

    alert('Pledge functionality would be implemented here. Amount: ' + amount);
}

function saveFunding() {
    alert('Save functionality would be implemented here');
}

function showError(message) {
    document.getElementById('projectDetail').innerHTML = `
        <div class="bg-red-50 border border-red-300 rounded-lg p-6 text-center">
            <p class="text-red-600">${message}</p>
        </div>
    `;
}

// Load project on page load
loadProjectDetail();
</script>
@endsection
