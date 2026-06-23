# World Wide Adverts Communities API Documentation

## Overview

The Communities API provides a social layer on top of the World Wide Adverts directory, allowing users to:
- Join and follow communities based on categories and locations
- Create and participate in discussions
- Comment on ads and posts
- Build reputation through community engagement
- Save and share content

## Base URL

```
/api/v1
```

## Authentication

Most endpoints require JWT authentication. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Communities Endpoints

### Get All Communities

**GET** `/communities`

Query Parameters:
- `category_id` (uuid, optional) - Filter by category
- `scope` (string, optional) - Filter by scope: `global`, `region`, `city`
- `region` (string, optional) - Filter by region
- `city` (string, optional) - Filter by city
- `verified` (boolean, optional) - Show only verified communities
- `featured` (boolean, optional) - Show only featured communities
- `sort` (string, optional) - Sort by: `newest`, `trending`, `members`, `posts`
- `search` (string, optional) - Search by name or description
- `per_page` (integer, optional) - Results per page (default: 20)

Response:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "community_id": "uuid",
        "name": "Property & Real Estate – UK",
        "slug": "property-real-estate-uk",
        "description": "A community for UK property buyers...",
        "category_id": "uuid",
        "scope": "region",
        "region": "UK",
        "members_count": 1250,
        "posts_count": 450,
        "is_verified": true,
        "is_featured": true,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ],
    "current_page": 1,
    "total": 100
  }
}
```

### Get Trending Communities

**GET** `/communities/trending`

Query Parameters:
- `limit` (integer, optional) - Number of results (default: 10)

### Get Featured Communities

**GET** `/communities/featured`

Query Parameters:
- `limit` (integer, optional) - Number of results (default: 10)

### Get Single Community

**GET** `/communities/{id}`

Response includes community details with members and category information.

### Create Community

**POST** `/communities` (Requires Authentication)

Request Body:
```json
{
  "name": "My Community",
  "description": "Community description",
  "category_id": "uuid",
  "cover_image": "https://example.com/image.jpg",
  "scope": "global",
  "region": "UK",
  "city": "London",
  "strict_moderation": true,
  "beginner_friendly": true,
  "rules": ["Rule 1", "Rule 2"]
}
```

### Update Community

**PUT** `/communities/{id}` (Requires Authentication)

Only community admins can update.

### Delete Community

**DELETE** `/communities/{id}` (Requires Authentication)

Only community admins can delete.

### Join Community

**POST** `/communities/{id}/join` (Requires Authentication)

### Leave Community

**POST** `/communities/{id}/leave` (Requires Authentication)

### Follow Community

**POST** `/communities/{id}/follow` (Requires Authentication)

### Unfollow Community

**POST** `/communities/{id}/unfollow` (Requires Authentication)

### Get Community Members

**GET** `/communities/{id}/members`

### Get My Communities

**GET** `/communities/my-communities` (Requires Authentication)

### Get Communities by Category

**GET** `/communities/category/{categoryId}`

## Community Posts Endpoints

### Get Feed

**GET** `/community-posts`

Query Parameters:
- `post_type` (string, optional) - Filter by type: `ad_thread`, `discussion_thread`
- `category_id` (uuid, optional) - Filter by category
- `location` (string, optional) - Filter by location
- `country` (string, optional) - Filter by country
- `city` (string, optional) - Filter by city
- `community_id` (uuid, optional) - Filter by community
- `verified_only` (boolean, optional) - Show only verified posts
- `sort` (string, optional) - Sort by: `newest`, `trending`, `top_rated`, `pinned`, `featured`, `sponsored`
- `search` (string, optional) - Search by title or content
- `per_page` (integer, optional) - Results per page (default: 20)

Response:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "post_id": "uuid",
        "user_id": "uuid",
        "post_type": "discussion_thread",
        "title": "Best practices for home buying",
        "content": "I'm looking to buy my first property...",
        "views_count": 150,
        "comments_count": 25,
        "reactions_count": 45,
        "saves_count": 12,
        "is_pinned": false,
        "is_featured": true,
        "created_at": "2024-01-01T00:00:00Z",
        "user": {
          "user_id": "uuid",
          "name": "John Doe",
          "avatar": "https://example.com/avatar.jpg"
        },
        "category": {
          "category_id": "uuid",
          "name": "Property & Real Estate"
        },
        "communities": [
          {
            "community_id": "uuid",
            "name": "Property & Real Estate – UK"
          }
        ]
      }
    ]
  }
}
```

### Get "For You" Feed

**GET** `/community-posts/for-you` (Requires Authentication)

Personalized feed based on user's activity and interests.

### Get "Following" Feed

**GET** `/community-posts/following` (Requires Authentication)

Posts from communities the user follows.

### Get "Local" Feed

**GET** `/community-posts/local` (Requires Authentication)

Posts from user's local area.

### Get Single Post

**GET** `/community-posts/{id}`

Increments view count automatically.

### Create Post

**POST** `/community-posts` (Requires Authentication)

Request Body:
```json
{
  "post_type": "discussion_thread",
  "title": "Post title",
  "content": "Post content",
  "cover_image": "https://example.com/image.jpg",
  "media": ["image1.jpg", "image2.jpg"],
  "category_id": "uuid",
  "location": "London, UK",
  "country": "United Kingdom",
  "city": "London",
  "discussion_type": "question",
  "tags": ["tag1", "tag2"],
  "community_ids": ["uuid1", "uuid2"]
}
```

For Ad Threads:
```json
{
  "post_type": "ad_thread",
  "advert_type": "property",
  "advert_id": "uuid",
  "title": "Property title",
  "community_ids": ["uuid"]
}
```

### Update Post

**PUT** `/community-posts/{id}` (Requires Authentication)

Only post author can update.

### Delete Post

**DELETE** `/community-posts/{id}` (Requires Authentication)

Only post author can delete.

### React to Post

**POST** `/community-posts/{id}/react` (Requires Authentication)

Request Body:
```json
{
  "reaction_type": "like"
}
```

Reaction types: `like`, `love`, `laugh`, `helpful`, `disagree`

### Save Post

**POST** `/community-posts/{id}/save` (Requires Authentication)

Toggle save/unsave.

### Pin Post

**POST** `/community-posts/{id}/pin` (Requires Authentication)

Requires moderator/admin role.

### Flag Post

**POST** `/community-posts/{id}/flag` (Requires Authentication)

Request Body:
```json
{
  "reason": "Spam or inappropriate content"
}
```

### Get Saved Posts

**GET** `/community-posts/saved` (Requires Authentication)

### Get My Posts

**GET** `/community-posts/my-posts` (Requires Authentication)

## Comments Endpoints

### Get Comments for Post

**GET** `/comments/post/{postId}`

Query Parameters:
- `comment_type` (string, optional) - Filter by type: `question`, `review`, `tip`, `report_experience`, `general`
- `include_replies` (boolean, optional) - Include reply comments
- `per_page` (integer, optional) - Results per page (default: 20)

Response:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "comment_id": "uuid",
        "post_id": "uuid",
        "user_id": "uuid",
        "parent_id": null,
        "content": "Great question! Here's my advice...",
        "comment_type": "advice",
        "reactions_count": 15,
        "replies_count": 3,
        "created_at": "2024-01-01T00:00:00Z",
        "user": {
          "user_id": "uuid",
          "name": "Jane Smith"
        }
      }
    ]
  }
}
```

### Get Single Comment

**GET** `/comments/{id}`

### Create Comment

**POST** `/comments` (Requires Authentication)

Request Body:
```json
{
  "post_id": "uuid",
  "parent_id": "uuid",
  "content": "Comment content",
  "comment_type": "advice"
}
```

### Update Comment

**PUT** `/comments/{id}` (Requires Authentication)

Only comment author can update.

### Delete Comment

**DELETE** `/comments/{id}` (Requires Authentication)

Only comment author can delete.

### React to Comment

**POST** `/comments/{id}/react` (Requires Authentication)

Request Body:
```json
{
  "reaction_type": "helpful"
}
```

### Get Comment Replies

**GET** `/comments/{id}/replies`

### Flag Comment

**POST** `/comments/{id}/flag` (Requires Authentication)

Request Body:
```json
{
  "reason": "Inappropriate content"
}
```

### Hide Comment

**POST** `/comments/{id}/hide` (Requires Authentication)

Requires moderator/admin role.

## Data Models

### Community

```json
{
  "community_id": "uuid",
  "name": "string",
  "slug": "string",
  "description": "string",
  "category_id": "uuid",
  "cover_image": "string",
  "scope": "global|region|city",
  "region": "string",
  "city": "string",
  "members_count": "integer",
  "posts_count": "integer",
  "active_ads_count": "integer",
  "is_verified": "boolean",
  "is_featured": "boolean",
  "strict_moderation": "boolean",
  "beginner_friendly": "boolean",
  "rules": ["string"],
  "created_by": "uuid",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

### CommunityPost

```json
{
  "post_id": "uuid",
  "user_id": "uuid",
  "post_type": "ad_thread|discussion_thread",
  "advert_type": "string",
  "advert_id": "uuid",
  "title": "string",
  "content": "string",
  "cover_image": "string",
  "media": ["string"],
  "views_count": "integer",
  "comments_count": "integer",
  "reactions_count": "integer",
  "saves_count": "integer",
  "shares_count": "integer",
  "is_pinned": "boolean",
  "is_featured": "boolean",
  "is_sponsored": "boolean",
  "is_verified": "boolean",
  "is_flagged": "boolean",
  "flag_reason": "string",
  "tags": ["string"],
  "category_id": "uuid",
  "location": "string",
  "country": "string",
  "city": "string",
  "discussion_type": "general|question|review|advice|report",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

### Comment

```json
{
  "comment_id": "uuid",
  "post_id": "uuid",
  "user_id": "uuid",
  "parent_id": "uuid",
  "content": "string",
  "comment_type": "question|review|tip|report_experience|general",
  "reactions_count": "integer",
  "replies_count": "integer",
  "is_flagged": "boolean",
  "flag_reason": "string",
  "is_hidden": "boolean",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

## Reputation System

Users earn reputation points through community engagement:
- Create post: +5 points
- Create comment: +2 points
- Receive helpful reaction: +2 points
- Join community: +1 point

Reputation affects:
- User visibility in communities
- Trust score for listings
- Access to certain features

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["error message"]
  }
}
```

Common HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Installation & Setup

### Run Migrations

```bash
php artisan migrate
```

### Run Seeders

```bash
php artisan db:seed --class=CommunitiesSeeder
php artisan db:seed --class=CommunityPostsSeeder
php artisan db:seed --class=CommentsSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```

## Testing

Use tools like Postman or cURL to test the endpoints. Make sure to:
1. Register/login to get JWT token
2. Include token in Authorization header for authenticated endpoints
3. Use valid UUIDs for related resources

## Future Enhancements

- Real-time notifications for comments and reactions
- Advanced search with filters
- Community analytics dashboard
- Badges and achievements system
- Direct messaging between users
- Event creation within communities
- Polls and surveys
- Rich media support (videos, documents)
- Multi-language support
