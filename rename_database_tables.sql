-- SQL script to remove 'ea_' prefix from all database tables
-- Run this script to rename existing tables in your database

-- Books tables
RENAME TABLE ea_books TO books;
RENAME TABLE ea_book_categories TO book_categories;
RENAME TABLE ea_book_purchases TO book_purchases;
RENAME TABLE ea_book_saves TO book_saves;
RENAME TABLE ea_book_upsells TO book_upsells;

-- Service tables
RENAME TABLE ea_services TO services;
RENAME TABLE ea_service_categories TO service_categories;
RENAME TABLE ea_service_media TO service_media;
RENAME TABLE ea_service_packages TO service_packages;
RENAME TABLE ea_service_addons TO service_addons;
RENAME TABLE ea_service_providers TO service_providers;
RENAME TABLE ea_service_promotions TO service_promotions;

-- Affiliate tables
RENAME TABLE ea_affiliate_links TO affiliate_links;
RENAME TABLE ea_affiliate_posts TO affiliate_posts;
RENAME TABLE ea_affiliate_post_upsells TO affiliate_post_upsells;
RENAME TABLE ea_affiliate_upsell_plans TO affiliate_upsell_plans;

-- User and customer tables
RENAME TABLE ea_users TO users;
RENAME TABLE ea_customer TO customer;
RENAME TABLE ea_customer_business TO customer_business;
RENAME TABLE ea_customer_store TO customer_store;
RENAME TABLE ea_user_analytics TO user_analytics;

-- Venue and event tables
RENAME TABLE ea_venues TO venues;
RENAME TABLE ea_venue_services TO venue_services;
RENAME TABLE ea_events TO events;

-- Banner tables
RENAME TABLE ea_banner TO banner;
RENAME TABLE ea_banner_ads TO banner_ads;
RENAME TABLE ea_banner_categories TO banner_categories;

-- Listing tables
RENAME TABLE ea_listing TO listing;
RENAME TABLE ea_listing_analytics TO listing_analytics;
RENAME TABLE ea_listing_favorite TO listing_favorite;
RENAME TABLE ea_listing_image TO listing_image;
RENAME TABLE ea_listing_upsells TO listing_upsells;

-- Job tables
RENAME TABLE ea_job_alerts TO job_alerts;
RENAME TABLE ea_job_upsells TO job_upsells;

-- Candidate tables
RENAME TABLE ea_candidate_profiles TO candidate_profiles;
RENAME TABLE ea_candidate_upsells TO candidate_upsells;

-- Resorts and travel tables
RENAME TABLE ea_resorts_travel_adverts TO resorts_travel_adverts;
RENAME TABLE ea_resorts_travel_categories TO resorts_travel_categories;

-- Other tables
RENAME TABLE ea_authors TO authors;
RENAME TABLE ea_analytics_reports TO analytics_reports;
RENAME TABLE ea_campaign TO campaign;
RENAME TABLE ea_dashboard_permissions TO dashboard_permissions;
RENAME TABLE ea_donor TO donor;
RENAME TABLE ea_group TO group;
RENAME TABLE ea_blog TO blog;
RENAME TABLE ea_location TO location;
RENAME TABLE ea_revenue_tracking TO revenue_tracking;
RENAME TABLE ea_staff_management TO staff_management;
RENAME TABLE ea_system_analytics TO system_analytics;
RENAME TABLE ea_advertisement TO advertisement;

-- Update foreign key constraints that might reference the old table names
-- Note: Some constraints might need to be dropped and recreated

-- Example for updating foreign key constraints (you may need to adjust based on your actual schema):
-- ALTER TABLE some_table DROP FOREIGN KEY fk_constraint_name;
-- ALTER TABLE some_table ADD CONSTRAINT fk_constraint_name FOREIGN KEY (column) REFERENCES new_table_name(id);

-- After renaming tables, you should also update any stored procedures, views, or triggers
-- that reference the old table names.
