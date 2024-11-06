# Installation

Update `composer.json` by adding this to the `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/ohmediaorg/news-bundle"
}
```

Then run `composer require ohmediaorg/news-bundle:dev-main`.

Import the routes in `config/routes.yaml`:

```yaml
oh_media_news:
    resource: '@OHMediaNewsBundle/config/routes.yaml'
```

Create the following config file, enable/disable tags based on site requirements. `config/packages/oh_media_news.yaml`:
```yaml
oh_media_news:
  article_tags: true
```

Run `php bin/console make:migration` then run the subsequent migration.

# Integration

The `news-bundle` is expected to integrate with the `page-bundle` via placing
the `news()` shortcodes inside a page's WYSIWYG content.

## Listing Template

The listing template can be implemented by creating
`templates/OHMediaNewsBundle/news_listing.html.twig`. This template is passed
three variables: `pagination`, `news_page_path` and `tags`. Here is a basic implementation:

```twig
{% if tags %}
  <div id="tags">
    {% for tag in tags %}
      <a href="{{ tag.href }}" {%if tag.active %}class='active'{% endif %}>{{ tag.name }}</a>
    {% endfor %}

  </div>
{% endif %}

<div id="news">
  {% if pagination.results|length > 0 %}
    {% for article in pagination.results %}
      {{ dump(article) }}
    {% endfor %}
  {% else %}
    <p>No articles</p>
  {% endif %}
</div>

{{ bootstrap_pagination(pagination) }}
{{ bootstrap_pagination_info(pagination) }}

```

## Item Template

The item template can be implemented by creating
`templates/OHMediaNewsBundle/news_item.html.twig`. This template is passed
two variables: `article` and `news_page_path`. Here is a basic implementation:

```twig
{{ dump(article) }}

<a href="page_path(news_page_path)">View All</a>
```
## Recent News   
   
The recent news can be implemented by inserting the `recent_articles()` tag within a template and creating
`templates/OHMediaNewsBundle/recent_news.html.twig`. This template is passed
two variables: `articles` and `news_page_path`. Here is a basic implementation:

```twig
  {% for article in articles %}
    {{ dump(article) }}
  {% endfor %}
  <a href="page_path(news_page_path)">View All</a>
```
