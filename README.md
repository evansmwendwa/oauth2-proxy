# oAuth2 Authentication Proxy for JavaScript Applications

Deploy an oAuth2 authentication proxy for your JavaScript SPA applications.
This app allows you to protect your `client_id` and `client_secret` and maintain a csrf
protected session using cookies.

**NB:** This application is built using [Slim Framework](https://www.slimframework.com/) and tested using an oAuth2 implementation for [Laravel Passport](https://laravel.com/docs/5.6/passport)

If you have any ideas, concerns e.t.c please open a pull request. Contribution is highly encouraged.

## Deploying with Nginx

**NB:** This application must be deployed in the same domain name as your SPA application in order
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

Both routes are protected with a csrf token. The csrf token will be passed via a cookie `XSRF-TOKEN` the first time you make a request to `/refresh`. After that every concecutive request must provide the csrf token via an header `X-XSRF-TOKEN`. This is done in order to protect the csrf token from being passed to the proxy without a CORS request.

**Invalid CSRF token response**

```json
"authenticated": false,
"token": {
  "error": "csrf_validation_failure",
  "message": "Missing or invalid csrf token"
}

```

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

If any request to `/refresh` returns `authenticated:false` then redirect the user to `/login` so that they can authenticate the session using a username and password

```
/refresh
```

#### Authenticated Response

All Authenticated responses from wither `/login` or `/refresh` will return the following type of response.

```
{
    "authenticated": true,
    "token": {
        "token_type": "Bearer",
        "expires_in": 18000,
        "access_token": "eyJ0eXAiOiJKV1Qi3QLaifYLsJrtlSQz1JFoGhnOKSoSLJ7ji-tZnFWYsNvdBXS_5lN_sWrAQsOZHdvui7Q918V-GAr7Ele7M"
    }
}
```
