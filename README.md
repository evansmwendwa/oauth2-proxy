# oAuth2 Authentication Proxy for JavaScript Applications

Deploy an oAuth2 authentication proxy for your JavaScript SPA applications.
This app allows you to protect your `client_id` and `client_secret` and maintain a csrf
protected session using cookies.

## Deploying with Nginx

**NB:** This app must be deployed in the same domain name as your SPA application in order
to bypass CORS (`This is for security measures - if you have to enable CORS then this might not be the correct solution for you`)

**NB:** It's highly recommended you run this proxy app under HTTPS

### Deploy the App

Clone the App to your server (If you have to do modifications to the code the fork it from the repo)

```
git clone https://github.com/evansmwendwa/oauth2-proxy.git
```

Install Dependencies using Composer

```
cd oauth2-proxy
composer install
```

Generate `.env` file
```
cp .env.dist .env
```

Update your `.env` file to include your `client_id` and `client_secret`

### Update your Nginx conf
**NB:** Optimize your nginx conf to your preferences. The only requirement here is for both your SPA app and the proxy app to run in the same domain name

```
server {

    # ....
    root /static/files/build
    try_files $uri /index.html?$query_string;

    location /proxy {
        alias /proxy/app/public;
        try_files $uri $uri/ $uri/index.php?$args;
        fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
    }
}
```

### Localhost testing

You can run the proxy using php cli server for testing which will run the app in http://localhost:9000 we have included a bash script to make this easy.

```
bash start.sh
```

### Authentication Routes

#### Login (POST)

```
/login
```

**Params**

```json
{
  "username": "username",
  "password": "password"
}
```

**Response**

```json
{
  "authenticated": true,
  "token": "token"
}
```

#### Refresh (GET)

In order to request a refresh token you must provide the `XSRF-TOKEN` provided to you via a cookie in the request header `X-XSRF-TOKEN`

If the request is missing or has an invalid `X-XSRF-TOKEN` then you will get an error response as below.

If any request to `/refresh` returns `authenticated:false` then redirect the user to login and the session will be created, hence storing a new refresh token in the proxy server session which will be used to refresh the access_token.

```json
"authenticated": false,
"token": {
  "error": "csrf_validation_failure",
  "message": "Missing or invalid csrf token"
}

```

```
/refresh
```
