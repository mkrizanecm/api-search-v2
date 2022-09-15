### SETUP

### Clone Repository

- Clone repository `https://github.com/mkrizanecm/api-search-v2.git`

### Install Composer

- Run **composer install** command on the project

### Change .env File Settings

- Change database URL to your credentials, while developing PostgreSQL was used
- Setup GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET
- Setup TWITTER_CLIENT_ID and TWITTER_CLIENT_SECRET just for testing the possibility to user different providers

### Database

- Create database with Doctrine **https://symfony.com/doc/current/doctrine.html**
- Be vary of which driver you're using and setup by your choice, pgsql was used while Developing
- Providers table contains providers with endpoint URL-s and identificators
- **INSERT INTO public.provider (id, url, ident, created) VALUES (1, 'https://api.github.com/search/issues?q=', 'github', CURRENT_TIMESTAMP);**

### Start Local Server

- Navigate to project folder and execute **php -S 127.0.0.1:8000 -t public** command

### Request Examples

### Help **http://localhost:8000/api**

### Help for specific provider **http://localhost:8000/api/github**

### Search term example 1 for GitHub provider **http://localhost:8000/api/github/php**)

### Search term example 2 for GitHub provider **http://localhost:8000/api/github/symfony**)

### Search term example 3 for GitHub provider **http://localhost:8000/api/github/postgres**)
