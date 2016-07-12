# Browser Games Portal

## What?
Basically, this application is a catalogue of playable games. The application can detect device type and show only supported games accordingly.
By default it looks like this:
![2](https://cloud.githubusercontent.com/assets/10897933/16771244/768f5c56-4858-11e6-8843-72bee805333c.png)

The application is responsive, but it needs some tuning to work perfectly.
![1](https://cloud.githubusercontent.com/assets/10897933/16771243/766e2842-4858-11e6-965a-4911d411ee6f.png)

![3](https://cloud.githubusercontent.com/assets/10897933/16771248/769a2000-4858-11e6-8be1-5de036c29744.png)

![4](https://cloud.githubusercontent.com/assets/10897933/16771246/76993406-4858-11e6-9140-72fe5d4ad53d.png)

![5](https://cloud.githubusercontent.com/assets/10897933/16771249/76a0a0ba-4858-11e6-8485-3f1a786414f4.png)

![6](https://cloud.githubusercontent.com/assets/10897933/16771247/7699b53e-4858-11e6-9f52-b7dd33b992a4.png)

![7](https://cloud.githubusercontent.com/assets/10897933/16771245/7698174c-4858-11e6-8252-b583f982678f.png)

![8](https://cloud.githubusercontent.com/assets/10897933/16771250/76ac1c56-4858-11e6-8772-d061614b9b6c.png)

![9](https://cloud.githubusercontent.com/assets/10897933/16771251/76b6095a-4858-11e6-9e5c-4bc2905ea7b3.png)

![10](https://cloud.githubusercontent.com/assets/10897933/16771252/76b75bac-4858-11e6-8f27-d70a96e4e7f5.png)

![sign](https://cloud.githubusercontent.com/assets/10897933/16771776/7337c67c-485a-11e6-9a7e-642f97721fd9.png)

You really want to apply a different design or, at least, change typography and colors.

Admin area is present (in highly experimental state)
![admin](https://cloud.githubusercontent.com/assets/10897933/16771582/b4afa5ee-4859-11e6-9464-0bf146ed92ae.png)

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