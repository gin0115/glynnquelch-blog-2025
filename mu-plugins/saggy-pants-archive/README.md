# Saggy Pants Archive

WordPress MU plugin that registers custom post types and taxonomies for Saggy Pants archive content.

## Features

### Custom Post Type: `sp-archive`

- **Hierarchical**: Yes (supports parent/child organization)
- **Supports**: Title, Editor (Gutenberg), Featured Image, Custom Fields, Page Attributes
- **Taxonomies**: Archive Types, Bands, Post Tags, Categories
- **Has Archive**: Yes
- **Menu Icon**: Archive icon

### Custom Taxonomy: `archive-type`

Hierarchical taxonomy for categorizing different types of archive content:

- Gig Reviews
- CD Reviews
- Interviews
- Articles
- Podcasts
- Albums
- Flyers

### Custom Taxonomy: `sp-band`

Non-hierarchical (tag-like) taxonomy for bands featured in archive content.

## Usage

This MU plugin is automatically loaded by WordPress. No activation required.

The custom post type and taxonomies are available in the WordPress admin immediately after the plugin is loaded.

## Post Tags and Categories

The `sp-archive` post type also supports:

- **Post Tags**: For tagging venues, contributors, locations, etc.
- **Categories**: For additional organization

## Archive Content

Archive content is imported from vintage HTML files using the Saggy Pants Importer plugin (separate temporary plugin used for parsing and importing).

