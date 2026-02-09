# Updated Books Section for WWW API Collection

Replace the existing "Books" section (lines 8448-8949) with the following comprehensive book endpoints:

```json
{
	"name": "Books",
	"item": [
		{
			"name": "Get Books (with filters)",
			"request": {
				"method": "GET",
				"header": [
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books?genre=education&book_type=pdf&author=john&min_price=10&max_price=50&search=programming&sort=newest&per_page=20",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books"
					],
					"query": [
						{
							"key": "genre",
							"value": "education",
							"description": "Filter by genre (action, education, drama, thriller, etc.)"
						},
						{
							"key": "book_type",
							"value": "pdf",
							"description": "Filter by book type (physical, pdf, audiobook)"
						},
						{
							"key": "author",
							"value": "john",
							"description": "Filter by author name"
						},
						{
							"key": "min_price",
							"value": "10",
							"description": "Minimum price filter"
						},
						{
							"key": "max_price",
							"value": "50",
							"description": "Maximum price filter"
						},
						{
							"key": "search",
							"value": "programming",
							"description": "Search in title, description, author, ISBN"
						},
						{
							"key": "sort",
							"value": "newest",
							"description": "Sort options (newest, oldest, price_low, price_high, relevance, author_az, title_az)"
						},
						{
							"key": "per_page",
							"value": "20",
							"description": "Results per page (1-50, default: 20)"
						}
					]
				},
				"description": "Get books with advanced filtering and search capabilities"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"url": {
							"raw": "{{base_url}}/api/v1/books",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books"
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"data\": [\n    {\n      \"listing_id\": 1,\n      \"title\": \"Sample Book Title\",\n      \"description\": \"Book description...\",\n      \"price\": 29.99,\n      \"book_type\": \"pdf\",\n      \"genre\": \"education\",\n      \"author\": \"John Doe\",\n      \"isbn\": \"978-1234567890\",\n      \"format\": \"e_book\",\n      \"condition\": \"new\",\n      \"is_downloadable\": true,\n      \"file_size\": 5242880,\n      \"formatted_file_size\": \"5 MB\",\n      \"customer\": {...},\n      \"location\": {...},\n      \"category\": {...},\n      \"created_at\": \"2026-01-22T10:00:00Z\"\n    }\n  ],\n  \"meta\": {\n    \"current_page\": 1,\n    \"last_page\": 5,\n    \"per_page\": 20,\n    \"total\": 100,\n    \"filters\": {\n      \"genres\": {\n        \"action\": \"Action\",\n        \"education\": \"Education\",\n        \"drama\": \"Drama\",\n        \"thriller\": \"Thriller\",\n        \"fiction\": \"Fiction\",\n        \"non_fiction\": \"Non-Fiction\",\n        \"textbook\": \"Textbook\",\n        \"romance\": \"Romance\",\n        \"mystery\": \"Mystery\",\n        \"scifi\": \"Sci-Fi\",\n        \"fantasy\": \"Fantasy\",\n        \"biography\": \"Biography\",\n        \"self_help\": \"Self-Help\",\n        \"business\": \"Business\",\n        \"children\": \"Children\"\n      },\n      \"book_types\": {\n        \"physical\": \"Physical Books\",\n        \"pdf\": \"PDF Downloads\",\n        \"audiobook\": \"Audiobooks\"\n      },\n      \"formats\": {\n        \"physical\": \"Physical Book\",\n        \"e_book\": \"E-book\",\n        \"audiobook\": \"Audiobook\"\n      },\n      \"conditions\": {\n        \"new\": \"New\",\n        \"like_new\": \"Like New\",\n        \"good\": \"Good\",\n        \"fair\": \"Fair\"\n      }\n    }\n  }\n}"
				}
			]
		},
		{
			"name": "Get Book Details",
			"request": {
				"method": "GET",
				"header": [
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books/:id",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						":id"
					],
					"variable": [
						{
							"key": "id",
							"value": "1"
						}
					]
				},
				"description": "Get detailed information about a specific book"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"url": {
							"raw": "{{base_url}}/api/v1/books/:id",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								":id"
							],
							"variable": [
								{
									"key": "id",
									"value": "1"
								}
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"data\": {\n    \"listing_id\": 1,\n    \"title\": \"Sample Book Title\",\n    \"description\": \"Comprehensive book description...\",\n    \"price\": 29.99,\n    \"book_type\": \"pdf\",\n    \"genre\": \"education\",\n    \"author\": \"John Doe\",\n    \"isbn\": \"978-1234567890\",\n    \"format\": \"e_book\",\n    \"condition\": \"new\",\n    \"is_downloadable\": true,\n    \"file_path\": \"books/sample_book.pdf\",\n    \"file_type\": \"pdf\",\n    \"file_size\": 5242880,\n    \"formatted_file_size\": \"5 MB\",\n    \"file_url\": \"http://example.com/storage/books/sample_book.pdf\",\n    \"website_url\": \"https://example.com/external-book\",\n    \"download_count\": 15,\n    \"last_downloaded_at\": \"2026-01-22T09:30:00Z\",\n    \"total_revenue\": 449.85,\n    \"total_downloads\": 15,\n    \"customer\": {...},\n    \"location\": {...},\n    \"category\": {...},\n    \"book_purchases\": [...],\n    \"created_at\": \"2026-01-22T10:00:00Z\"\n  }\n}"
				}
			]
		},
		{
			"name": "Create Book Listing",
			"request": {
				"auth": {
					"type": "bearer",
					"bearer": [
						{
							"key": "token",
							"value": "{{auth_token}}",
							"type": "string"
						}
					]
				},
				"method": "POST",
				"header": [
					{
						"key": "Content-Type",
						"value": "application/json"
					},
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"body": {
					"mode": "raw",
					"raw": "{\n  \"title\": \"My New Book\",\n  \"description\": \"A comprehensive description of the book...\",\n  \"price\": 19.99,\n  \"book_type\": \"pdf\",\n  \"genre\": \"education\",\n  \"author\": \"Jane Smith\",\n  \"isbn\": \"978-0987654321\",\n  \"format\": \"e_book\",\n  \"condition\": \"new\",\n  \"website_url\": \"https://mywebsite.com/book\",\n  \"is_downloadable\": true,\n  \"location_id\": 1\n}"
				},
				"url": {
					"raw": "{{base_url}}/api/v1/books",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books"
					]
				},
				"description": "Create a new book listing (requires authentication)"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "POST",
						"header": [
							{
								"key": "Content-Type",
								"value": "application/json"
							},
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{auth_token}}",
									"type": "string"
								}
							]
						},
						"body": {
							"mode": "raw",
							"raw": "{\n  \"title\": \"My New Book\",\n  \"description\": \"A comprehensive description of the book...\",\n  \"price\": 19.99,\n  \"book_type\": \"pdf\",\n  \"genre\": \"education\",\n  \"author\": \"Jane Smith\",\n  \"isbn\": \"978-0987654321\",\n  \"format\": \"e_book\",\n  \"condition\": \"new\",\n  \"website_url\": \"https://mywebsite.com/book\",\n  \"is_downloadable\": true,\n  \"location_id\": 1\n}"
						},
						"url": {
							"raw": "{{base_url}}/api/v1/books",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books"
							]
						}
					},
					"status": "Created",
					"code": 201,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"message\": \"Book listing created successfully\",\n  \"data\": {\n    \"listing_id\": 123,\n    \"title\": \"My New Book\",\n    \"description\": \"A comprehensive description of the book...\",\n    \"price\": 19.99,\n    \"book_type\": \"pdf\",\n    \"genre\": \"education\",\n    \"author\": \"Jane Smith\",\n    \"isbn\": \"978-0987654321\",\n    \"format\": \"e_book\",\n    \"condition\": \"new\",\n    \"is_downloadable\": true,\n    \"website_url\": \"https://mywebsite.com/book\",\n    \"location_id\": 1,\n    \"customer_id\": 1,\n    \"category_id\": 5,\n    \"status\": \"active\",\n    \"approval_status\": \"pending\",\n    \"created_at\": \"2026-01-22T10:00:00Z\",\n    \"updated_at\": \"2026-01-22T10:00:00Z\"\n  }\n}"
				}
			]
		},
		{
			"name": "Purchase Book",
			"request": {
				"auth": {
					"type": "bearer",
					"bearer": [
						{
							"key": "token",
							"value": "{{auth_token}}",
							"type": "string"
						}
					]
				},
				"method": "POST",
				"header": [
					{
						"key": "Content-Type",
						"value": "application/json"
					},
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"body": {
					"mode": "raw",
					"raw": "{\n  \"payment_method\": \"credit_card\"\n}"
				},
				"url": {
					"raw": "{{base_url}}/api/v1/books/:id/purchase",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						":id",
						"purchase"
					],
					"variable": [
						{
							"key": "id",
							"value": "1"
						}
					]
				},
				"description": "Purchase a book and receive download access (requires authentication)"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "POST",
						"header": [
							{
								"key": "Content-Type",
								"value": "application/json"
							},
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{auth_token}}",
									"type": "string"
								}
							]
						},
						"body": {
							"mode": "raw",
							"raw": "{\n  \"payment_method\": \"credit_card\"\n}"
						},
						"url": {
							"raw": "{{base_url}}/api/v1/books/:id/purchase",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								":id",
								"purchase"
							],
							"variable": [
								{
									"key": "id",
									"value": "1"
								}
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"message\": \"Book purchased successfully\",\n  \"data\": {\n    \"purchase_id\": 456,\n    \"download_url\": \"http://example.com/api/v1/books/download/abc123token\",\n    \"download_token\": \"abc123token\",\n    \"expires_at\": \"2026-01-29T10:00:00Z\"\n  }\n}"
				}
			]
		},
		{
			"name": "Download Book",
			"request": {
				"method": "GET",
				"header": [
					{
						"key": "Accept",
						"value": "application/octet-stream"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books/download/:token",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						"download",
						":token"
					],
					"variable": [
						{
							"key": "token",
							"value": "abc123token"
						}
					]
				},
				"description": "Download a purchased book using the provided token"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/octet-stream"
							}
						],
						"url": {
							"raw": "{{base_url}}/api/v1/books/download/:token",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								"download",
								":token"
							],
							"variable": [
								{
									"key": "token",
									"value": "abc123token"
								}
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "raw",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/octet-stream"
						},
						{
							"key": "Content-Disposition",
							"value": "attachment; filename=\"Sample Book Title - John Doe.pdf\""
						}
					],
					"cookie": [],
					"body": "[Binary file content]"
				}
			]
		},
		{
			"name": "Get My Purchases",
			"request": {
				"auth": {
					"type": "bearer",
					"bearer": [
						{
							"key": "token",
							"value": "{{auth_token}}",
							"type": "string"
						}
					]
				},
				"method": "GET",
				"header": [
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books/my-purchases",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						"my-purchases"
					]
				},
				"description": "Get the current customer's purchased books (requires authentication)"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{auth_token}}",
									"type": "string"
								}
							]
						},
						"url": {
							"raw": "{{base_url}}/api/v1/books/my-purchases",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								"my-purchases"
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"data\": [\n    {\n      \"purchase_id\": 456,\n      \"listing_id\": 1,\n      \"price_paid\": 29.99,\n      \"payment_status\": \"completed\",\n      \"download_token\": \"abc123token\",\n      \"download_token_expires_at\": \"2026-01-29T10:00:00Z\",\n      \"total_downloads\": 3,\n      \"first_downloaded_at\": \"2026-01-22T10:05:00Z\",\n      \"last_downloaded_at\": \"2026-01-22T15:30:00Z\",\n      \"listing\": {\n        \"listing_id\": 1,\n        \"title\": \"Sample Book Title\",\n        \"author\": \"John Doe\",\n        \"price\": 29.99,\n        \"book_type\": \"pdf\",\n        \"genre\": \"education\"\n      },\n      \"created_at\": \"2026-01-22T10:00:00Z\"\n    }\n  ],\n  \"meta\": {\n    \"current_page\": 1,\n    \"last_page\": 1,\n    \"per_page\": 20,\n    \"total\": 5\n  }\n}"
				}
			]
		},
		{
			"name": "Get Book Statistics (Admin)",
			"request": {
				"auth": {
					"type": "bearer",
					"bearer": [
						{
							"key": "token",
							"value": "{{admin_token}}",
							"type": "string"
						}
					]
				},
				"method": "GET",
				"header": [
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books/statistics",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						"statistics"
					]
				},
				"description": "Get comprehensive book statistics (requires admin authentication)"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "GET",
						"header": [
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"auth": {
							"type": "bearer",
							"bearer": [
								{
									"key": "token",
									"value": "{{admin_token}}",
									"type": "string"
								}
							]
						},
						"url": {
							"raw": "{{base_url}}/api/v1/books/statistics",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								"statistics"
							]
						}
					},
					"status": "OK",
					"code": 200,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n  \"data\": {\n    \"total_books\": 150,\n    \"active_books\": 120,\n    \"total_purchases\": 500,\n    \"total_revenue\": 12500.50,\n    \"total_downloads\": 850,\n    \"books_by_type\": {\n      \"physical\": 80,\n      \"pdf\": 50,\n      \"audiobook\": 20\n    },\n    \"books_by_genre\": {\n      \"education\": 40,\n      \"fiction\": 35,\n      \"business\": 25\n    },\n    \"recent_purchases\": [\n      {\n        \"purchase_id\": 456,\n        \"price_paid\": 29.99,\n        \"listing\": {...},\n        \"customer\": {...},\n        \"created_at\": \"2026-01-22T10:00:00Z\"\n      }\n    ]\n  }\n}"
				}
			]
		},
		{
			"name": "Scrape Books",
			"request": {
				"method": "POST",
				"header": [
					{
						"key": "Accept",
						"value": "application/json"
					}
				],
				"url": {
					"raw": "{{base_url}}/api/v1/books/scrape",
					"host": [
						"{{base_url}}"
					],
					"path": [
						"api",
						"v1",
						"books",
						"scrape"
					]
				},
				"description": "Scrape book data from external sources"
			},
			"response": [
				{
					"name": "Success Response",
					"originalRequest": {
						"method": "POST",
						"header": [
							{
								"key": "Accept",
								"value": "application/json"
							}
						],
						"url": {
							"raw": "{{base_url}}/api/v1/books/scrape",
							"host": [
								"{{base_url}}"
							],
							"path": [
								"api",
								"v1",
								"books",
								"scrape"
							]
						}
					},
					"status": "Created",
					"code": 201,
					"_postman_previewlanguage": "json",
					"header": [
						{
							"key": "Content-Type",
							"value": "application/json"
						}
					],
					"cookie": [],
					"body": "{\n    \"status\": \"Success\",\n    \"message\": \"Books scraped successfully\",\n    \"data\": {\n        \"scraped_count\": 25,\n        \"imported_count\": 20\n    }\n}"
				}
			]
		}
	],
	"description": "Comprehensive book management endpoints with PDF/audiobook support, purchase tracking, and download functionality"
},
```

## Key Changes Made:

1. **Updated URL paths**: Changed from `/api/v1/book` to `/api/v1/books` (plural)
2. **Added comprehensive filtering**: Genre, book type, author, price range, search, sorting
3. **Added purchase endpoints**: Purchase book, download with token, view purchases
4. **Added admin statistics**: Comprehensive book analytics
5. **Enhanced request/response examples**: Include all new book fields
6. **Added authentication requirements**: Where needed for customer/admin access
7. **Maintained scrape endpoint**: Kept existing scraping functionality

## Frontend Integration Notes:

### Environment Variables Needed:
```javascript
// In your frontend environment
BASE_URL=http://your-api-domain.com
AUTH_TOKEN=customer_jwt_token
ADMIN_TOKEN=admin_jwt_token
```

### API Usage Examples:

```javascript
// Get books with filters
const getBooks = async (filters) => {
  const params = new URLSearchParams(filters);
  const response = await fetch(`${BASE_URL}/api/v1/books?${params}`);
  return response.json();
};

// Create book listing
const createBook = async (bookData, token) => {
  const response = await fetch(`${BASE_URL}/api/v1/books`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(bookData)
  });
  return response.json();
};

// Purchase book
const purchaseBook = async (bookId, paymentMethod, token) => {
  const response = await fetch(`${BASE_URL}/api/v1/books/${bookId}/purchase`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ payment_method: paymentMethod })
  });
  return response.json();
};

// Download book
const downloadBook = async (downloadToken) => {
  const response = await fetch(`${BASE_URL}/api/v1/books/download/${downloadToken}`);
  return response.blob(); // Returns file blob for download
};
```

### File Upload Support:

For creating books with files, use FormData:

```javascript
const createBookWithFile = async (bookData, file, token) => {
  const formData = new FormData();
  Object.keys(bookData).forEach(key => {
    formData.append(key, bookData[key]);
  });
  if (file) {
    formData.append('file', file);
  }
  
  const response = await fetch(`${BASE_URL}/api/v1/books`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  return response.json();
};
```

This updated API collection provides comprehensive book functionality for your frontend application with all the features you requested.
