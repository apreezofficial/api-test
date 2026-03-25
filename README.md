# JSON Posts API

A simple REST API for creating and fetching posts using vanilla PHP with file-based JSON storage.

## Quick Start

```bash
# Using PHP built-in server
php -S localhost:8000
```

Then access: `http://localhost:8000/api/posts`

---

## Endpoints

### GET /api/posts

Fetch all posts with optional filtering.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `author` | string | Filter by author name |
| `limit` | int | Limit number of results |

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "abc123...",
            "title": "My First Post",
            "content": "Post content here...",
            "author": "John",
            "created_at": "2026-03-25T07:00:00+01:00",
            "updated_at": "2026-03-25T07:00:00+01:00"
        }
    ],
    "total": 1
}
```

**Examples:**
```
GET /api/posts              # All posts
GET /api/posts?limit=5     # Last 5 posts
GET /api/posts?author=John # Posts by John
```

---

### POST /api/posts

Create a new post.

**Request Body:**
```json
{
    "title": "My Post Title",
    "content": "This is the post content...",
    "author": "John"  // optional, defaults to "Anonymous"
}
```

**Validation Rules:**
- `title`: Required, 3-200 characters
- `content`: Required, minimum 10 characters
- `author`: Optional, max 100 characters

**Success Response (201):**
```json
{
    "success": true,
    "message": "Post created successfully",
    "data": {
        "id": "abc123...",
        "title": "My Post Title",
        "content": "This is the post content...",
        "author": "John",
        "created_at": "2026-03-25T07:00:00+01:00",
        "updated_at": "2026-03-25T07:00:00+01:00"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "errors": [
        "Title is required",
        "Content must be at least 10 characters"
    ]
}
```

---

## Example Usage

### cURL

```bash
# Create a post
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"Hello World","content":"This is my first post!","author":"Jane"}'

# Get all posts
curl http://localhost:8000/api/posts

# Get posts by author
curl "http://localhost:8000/api/posts?author=Jane"
```

---

## Tech Stack

- **PHP 8.0+** (vanilla, no frameworks)
- **JSON file** for storage (`db.json`)
- Built-in PHP development server

---

## File Structure

```
.
├── index.php    # Main API file
├── db.json      # Database file (auto-created)
└── README.md    # This documentation