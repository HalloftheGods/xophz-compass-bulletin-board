# Xophz Bulletin Board (`xophz-compass-bulletin-board`)

> **Category:** Trajectory · **Version:** 26.8.25

An enterprise-grade WordPress forum engine, topic dispatcher, and community board manager natively integrated with YouMeOS, the Noosphere Observer, and standard WordPress theme environments.

## Description

**Xophz Bulletin Board** provides out-of-the-box forum software built directly on WordPress primitives (Custom Post Types, Hierarchical Taxonomies, and Comment Threading). It acts as the single unified data engine powering:

- **The Noosphere Observer & Global Pulse:** Synchronized spheres (`noo/*`) and decentralized community pulse tracking.
- **YouMeOS Sparks:** Headless forum windows for Cafeteria ("Food for Thought"), Suggestion Box, and Lunch Room.
- **Standard WordPress Frontends:** Native REST API endpoints and shortcodes for non-app visitors.

## Core Capabilities

- **Topics & Boards Architecture:** Clean WP Admin UI for organizing hierarchical boards, categories, and discussion threads.
- **Term Metadata:** First-class support for custom FontAwesome icons (`board_icon`) and visual sort ordering (`board_order`).
- **REST Telemetry & Stats:** Aggregates reply counts, view counts, and latest activity timestamps across all boards and threads.
- **User Subscriptions:** User-level board synchronization persisted via `/wp-json/xophz-compass/v1/subscriptions`.
- **Pre-Seeded Namespaces:** Automated initialization for Noosphere (`noo`), Suggestion Box (`suggestion-box`), and Lunch Room (`lunch-room`).

## Requirements

- WordPress 5.8+, PHP 7.4+
- Compatible standalone or bundled with Xophz COMPASS

## REST API Endpoints

| Method | Route | Description |
|---|---|---|
| `GET` | `/wp-json/wp/v2/cafeteria_topic` | Query topic threads (supports pagination, search, board filters, `_embed`) |
| `POST` | `/wp-json/wp/v2/cafeteria_topic` | Create a new topic thread |
| `GET` | `/wp-json/wp/v2/cafeteria_board` | Query boards hierarchy and stats |
| `GET` | `/wp-json/xophz-compass/v1/subscriptions` | Retrieve user subscribed boards |
| `POST` | `/wp-json/xophz-compass/v1/subscriptions` | Update user subscribed boards |
| `GET` | `/wp-json/xophz-bulletin-board/v1/pulse` | Aggregated global activity pulse |

## License

GPL-2.0+
