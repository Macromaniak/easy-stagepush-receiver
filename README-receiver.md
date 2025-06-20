# Easy StagePush Receiver

**Easy StagePush Receiver** is the companion plugin for [Easy StagePush Sender](https://github.com/Macromaniak/easy-stagepush-sender). It listens for content pushed via REST API from a staging or development WordPress site and saves it to your live production site.

---

## 🚀 Purpose

This plugin acts as the "receiver" in the staging-to-production deployment pipeline. It accepts content, media, taxonomies, and metadata via an authenticated or open REST API endpoint and automatically creates or updates matching content on the production site.

---

## ✨ Features

- ✅ REST API endpoint to receive content from staging/dev
- ✅ Creates or updates posts, pages, and CPTs
- ✅ Accepts ACF fields including groups, repeaters, flex layouts
- ✅ Downloads and attaches featured images and other media
- ✅ Applies taxonomy terms (including custom taxonomies)
- ✅ Preserves parent/child page hierarchy
- ✅ Stores Yoast SEO metadata
- ✅ Supports SVG and other custom file types

---

## 🧩 Requirements

- WordPress 5.0+
- [Easy StagePush Sender](https://github.com/Macromaniak/easy-stagepush-sender) on your staging/dev site
- ACF Pro (if using ACF fields)

---

## 🔧 Installation

1. Install and activate **Easy StagePush Receiver** on your **production/live** site.
2. Install and configure **Easy StagePush Sender** on your **staging/dev** site.
3. Ensure your production site has the same:
   - Post types
   - Taxonomies
   - ACF field groups
4. You're ready to receive content!

---

## 📌 REST API Endpoint

```
POST /wp-json/dev-sync/v1/import-post
```

This endpoint accepts JSON payloads from the Sender plugin and handles all processing server-side.

---

## ⚠️ Important Notes

> This plugin assumes your production site **mirrors** your dev/staging site structurally.  
> It will not create custom post types, taxonomies, or ACF fields — those must already exist and be registered.

---

## 🛡️ Security

Currently the REST endpoint is unauthenticated for convenience. You are encouraged to protect access via:

- IP whitelisting
- Authentication tokens (planned)
- Server-level access rules

---

## 📫 Support

For help or feature requests:

- Email: anandhu.natesh@gmail.com / anandhu.nadesh@gmail.com
- GitHub: [Submit an issue](https://github.com/Macromaniak/easy-stagepush-receiver/issues)

---

## 🧪 Contributions

We welcome PRs and suggestions! Fork this repo and get started.

---

## 📄 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

Deploy safely and push clean! 🚀
