<?php
/**
 * JSON Posts API - A simple REST API for creating and fetching posts
 * Uses file-based JSON storage
 */

// CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database file path
define('DB_FILE', __DIR__ . '/db.json');

/**
 * Read posts from JSON file
 */
function getPosts(): array {
    if (!file_exists(DB_FILE)) {
        return [];
    }
    $content = file_get_contents(DB_FILE);
    return json_decode($content, true) ?? [];
}

/**
 * Save posts to JSON file
 */
function savePosts(array $posts): bool {
    return file_put_contents(DB_FILE, json_encode($posts, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Generate unique ID
 */
function generateId(): string {
    return bin2hex(random_bytes(8));
}

/**
 * Validate post data
 */
function validatePost(array $data): array {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    } elseif (strlen($data['title']) < 3) {
        $errors[] = 'Title must be at least 3 characters';
    } elseif (strlen($data['title']) > 200) {
        $errors[] = 'Title must be less than 200 characters';
    }
    
    if (empty($data['content'])) {
        $errors[] = 'Content is required';
    } elseif (strlen($data['content']) < 10) {
        $errors[] = 'Content must be at least 10 characters';
    }
    
    if (!empty($data['author']) && strlen($data['author']) > 100) {
        $errors[] = 'Author must be less than 100 characters';
    }
    
    return $errors;
}

/**
 * Send JSON response
 */
function jsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Route: GET /api/posts or POST /api/posts
if ($uri === '/api/posts' || $uri === '') {
    
    // GET - Fetch all posts
    if ($method === 'GET') {
        $posts = getPosts();
        
        // Support filtering by author
        if (isset($_GET['author'])) {
            $author = strtolower($_GET['author']);
            $posts = array_filter($posts, fn($p) => 
                isset($p['author']) && strtolower($p['author']) === $author
            );
            $posts = array_values($posts);
        }
        
        // Support limiting results
        if (isset($_GET['limit'])) {
            $limit = (int)$_GET['limit'];
            $posts = array_slice($posts, 0, $limit);
        }
        
        jsonResponse([
            'success' => true,
            'data' => $posts,
            'total' => count($posts)
        ]);
    }
    
    // POST - Create new post
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            jsonResponse([
                'success' => false,
                'error' => 'Invalid JSON body'
            ], 400);
        }
        
        // Validate input
        $errors = validatePost($input);
        if (!empty($errors)) {
            jsonResponse([
                'success' => false,
                'errors' => $errors
            ], 422);
        }
        
        // Create new post
        $posts = getPosts();
        $newPost = [
            'id' => generateId(),
            'title' => trim($input['title']),
            'content' => trim($input['content']),
            'author' => trim($input['author'] ?? 'Anonymous'),
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];
        
        $posts[] = $newPost;
        
        if (!savePosts($posts)) {
            jsonResponse([
                'success' => false,
                'error' => 'Failed to save post'
            ], 500);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $newPost
        ], 201);
    }
}

// 404 for unknown routes
jsonResponse([
    'success' => false,
    'error' => 'Endpoint not found'
], 404);
