# summerinspect

Official corporate website for **Summer Inspection & Logistique W/A LTD (SILWA)** (`https://summerinspectlog.com/`).

High-performance, semantic, and SEO/AI-search optimized web platform built without WordPress bloat.

## 🚀 Features
- **Ultra-Fast Performance**: Zero external heavy libraries, loading in under 1 second.
- **Interactive UI**: Hero image slider, services showcase, interactive petroleum density & loss calculators, fraud disclaimer popup, and mobile navigation.
- **Form Handling**: Asynchronous inquiry and quote submission handled via PHP (`send-mail.php`) with auto-responder receipts.
- **Google SEO & Search Console Ready**: Fully indexed (`robots.txt`, `sitemap.xml`, Geo-tags, JSON-LD Schema.org rich business data).
- **AI Search Ready (GEO/AIO)**: Includes `llms.txt` and crawler directives for ChatGPT, Claude, Gemini, and Perplexity.

## 📁 Project Structure
```text
summerinspect/
├── index.html                           # Semantic landing page & schema data
├── llms.txt                             # AI search knowledge manifest
├── sitemap.xml                          # XML sitemap with image metadata
├── robots.txt                           # Search engine & AI crawler crawling rules
├── send-mail.php                        # PHP mail handler
├── mail-test.php                        # Mail diagnostic script
├── .htaccess                            # Hostinger server config (HTTPS, GZIP, caching)
├── google-indexing-and-analytics-guide.md
└── assets/
    ├── css/
    │   └── style.css                    # Modular lightweight CSS
    ├── js/
    │   └── main.js                      # Carousel, calculators, modal & AJAX form
    └── images/                          # All local logos, photos & icons
```

## 🛠️ Deployment
Upload all files directly into your Hostinger `public_html/` folder. Form handling and caching work out of the box with Apache and PHP.
