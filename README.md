# Custom WordPress CMS

This repository contains a custom WordPress setup tailored for a headless CMS implementation. It includes a modified Twenty Twenty-One theme, integration with Advanced Custom Fields (ACF), and custom functionalities for handling events and contact forms. The site uses GraphQL for API interaction and is hosted on AWS Lightsail.

## Features

- **Lightweight** and optimized for performance.
- **GraphQL Integration** for flexible API usage.
- **Custom Post Types** for events and contact forms.
- **Custom Theme** based on Twenty Twenty-One with tailored modifications.

---

## Prerequisites

To set up the project locally, ensure you have the following installed:

1. [Docker](https://www.docker.com/products/docker-desktop) (required for `wp-env`).
2. Node.js and [pnpm](https://pnpm.io/) (for managing dependencies and running scripts).
3. [wp-env](https://developer.wordpress.org/news/2023/03/28/quick-and-easy-local-wordpress-development-with-wp-env/) (WordPress local environment).

---

## Installation and Setup

1. Clone this repository:

   ```bash
   git clone <repository-url>
   cd <repository-directory>
   ```

2. Install dependencies:

   ```bash
   pnpm install
   ```

3. Start the local WordPress environment:

   ```bash
   pnpm run wp-env start
   ```

4. Access the WordPress installation:

   - WordPress Admin: [http://localhost:8888/wp-admin](http://localhost:8888/wp-admin)
   - Frontend: [http://localhost:8888](http://localhost:8888)

5. Use the credentials provided in your `.wp-env.json` for accessing the admin dashboard.

---

## Development Workflow

### Working with the Custom Theme

The project uses a customized version of the Twenty Twenty-One theme, located in:

```plaintext
themes/twentytwentyone/
```

### Custom Functionality

- **Contact Form Post Type**: Defined in `themes/twentytwentyone/lib/contact-form-post-type.php`, with REST API integration and custom fields.
- **Events Management**: Defined in `themes/twentytwentyone/lib/events.php`, includes cron job support for managing event status.
- **GraphQL Integration**: Managed in `themes/twentytwentyone/lib/vendor-acf.php`.

### Modifying the Theme

To edit the theme:

1. Navigate to `themes/twentytwentyone/`.
2. Update files as needed, such as `functions.php` or custom libraries in `lib/`.
3. Changes will reflect immediately if using `wp-env`.

---

## Scripts

The following `pnpm` scripts are available:

- `pnpm run wp-env start`: Starts the local WordPress environment.
- `pnpm run wp-env stop`: Stops the environment.
- `pnpm run wp-env destroy`: Destroys the environment (use with caution).

---

## Debugging

Debugging is enabled by default for local development:

- Logs can be found in `wp-content/debug.log`.
- Adjust settings in `.wp-env.json` if needed.

---

## Directory Structure

- **`themes/twentytwentyone/`**: Contains the custom theme files.
- **`.wp-env.json`**: Configuration for the local WordPress environment.
- **`package.json`**: Node.js dependencies and scripts for managing the development environment.

---

## Notes

1. All uploads and sensitive data are excluded from version control via `.gitignore`.
2. For production deployments, refer to your AWS Lightsail setup documentation.
3. Future enhancements should follow WordPress development standards.

---

## Additional Resources

- [WordPress Plugin Development Handbook](https://developer.wordpress.org/plugins/)
- [Advanced Custom Fields Documentation](https://www.advancedcustomfields.com/resources/)
- [Docker Documentation](https://docs.docker.com/)

```

```
