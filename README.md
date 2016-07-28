# Browser Games Portal

## What?
Basically, this application is a catalogue of playable games. The application can detect device type and show only supported games accordingly.
It looks like this on different devices:

<a href="https://cloud.githubusercontent.com/assets/10897933/17218759/a4cb5246-54f1-11e6-9a7f-202e42a5ff76.jpg"><img src="https://cloud.githubusercontent.com/assets/10897933/17218759/a4cb5246-54f1-11e6-9a7f-202e42a5ff76.jpg" width="50"></img></a>
<a href="https://cloud.githubusercontent.com/assets/10897933/17218760/a4cbf1ce-54f1-11e6-9428-36117df38d6d.jpg"><img src="https://cloud.githubusercontent.com/assets/10897933/17218760/a4cbf1ce-54f1-11e6-9428-36117df38d6d.jpg" width="233"></img></a>
<a href="https://cloud.githubusercontent.com/assets/10897933/17218761/a4cc219e-54f1-11e6-9967-185a0d186b82.jpg"><img src="https://cloud.githubusercontent.com/assets/10897933/17218761/a4cc219e-54f1-11e6-9967-185a0d186b82.jpg" width="482"></img></a>

You really want to apply a different design or, at least, change typography and colors.

Games and categories could be fetched from:
- [Spilgames](http://www.spilgames.com/)
- [Arcade Game Feed](http://arcadegamefeed.com/)
- [2PG](http://www.2pg.com/)

any other provider could be easily added.

Users are able to connect via social networks, receive newsletters, bookmark, rate, comment and play games.

## How?

### Requirements
- PHP 7
- MySQL 5.6
- Redis 3
- NodeJS (compiling assets)

### Foundation
- [Zend Expressive](https://github.com/zendframework/zend-expressive) - scaffolding;
- [Twig](https://github.com/twigphp/Twig) - templating;
- [FastRoute](https://github.com/nikic/FastRoute) - routing;
- [Doctrine](https://github.com/doctrine/doctrine2) - database;
- [Phinx](https://github.com/robmorgan/phinx) - migrations;
- [Webpack](https://github.com/webpack/webpack) - assets;
- [VueJS](https://github.com/vuejs/vue) - front-end;

### Setup
The application can be launched as docker container. It's necessary to install all dependencies before:
```bash
composer install
npm install
```

Configure database connection, redis and else in `.env` file.

Create database and run migrations:
```bash
mysql -u root -e "CREATE DATABASE my_games_portal;"
phinx migrate
```

Compile front-end assets with:
```bash
webpack -p
```

And run:
```bash
docker-compose up
```

## Copyright and license
Code and documentation copyright 2016 Cudev Ltd. Code released under [the MIT license](https://github.com/cudev/browser-games-portal/blob/master/LICENSE).
