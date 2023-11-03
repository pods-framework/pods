Add a `cypress.env.json` file at Pods root directory, and update the `host`, `username`, `password` to match WordPress site that cypress is going to test on.

```json
{
  "host": "http://127.0.0.1:8000",
  "username": "your-wordpress-site-username",
  "password": "your-wordpress-site-password"
}
```