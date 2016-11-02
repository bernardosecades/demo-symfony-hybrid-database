[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bernardosecades/demo-symfony-hybrid-database/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bernardosecades/demo-symfony-hybrid-database/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9a6d3035-5c2d-4ad1-ae47-700057ddacd8/mini.png)](https://insight.sensiolabs.com/projects/9a6d3035-5c2d-4ad1-ae47-700057ddacd8)


Demo App - Example hybrid database: RDBMS (MySQL) and NoSql (Redis)
===================================================================

Objective of the demo: You have a website with users, and those users can write
comments. Add REST API to the backend for a new feature: the possibility for
users to rate comments written by other users.

Create 2 REST endpoints:

* One to rate a comment (a rating is a thumb up or a thumb down).
* One to get the total score (sum of ratings) for a comment.

We will have two tables in mysql (user and comment) and will save score comment in redis database.

To resolve this problem is enough with mysql database (create new table M:N for score comments), but this is only an example how use
NoSql database and RDBMS in symfony project.

To save score comments we will use sets, where the key of set will be `rating:{commentId}` and members of the set following
next format `{userId:score}`.

Example:

`smembers rating:1`

It will return,

```
1) "5:1"
2) "3:1"
3) "2:1"
4) "4:-1"
```

For sure you can resolve score comments using different structure and strategy, this is only an example.


Architecture
============

# Keys

- Use interfaces to get soft dependencies.
- Keep your business logic outside bundles.
- Keep your model decoupled from the framework and ORM.
- Keep mapping and validation configuration outside entity classes so do not use annotations in the model.
- Annotate your controller, Example: 'routing', 'security', 'template', 'cache', 'apidoc', ...

# Layers

* Business cases
* Repositories
* Services
* Controllers
* Value objects

(*) When I have time I will do the same demo application with DDD architecture.

## Business cases

In this layer should be saved logic of the product, you can have dependencies like
repositories, services, components or value objects, but you never can have dependency with another
business case.

**Dependencies allowed:**

 - Services
 - Value Objects
 - External libraries

**No dependencies allowed:**

 - ~~Business cases~~

(*) Important: never inject object like session in this layer. Business cases are used in controllers but as well in commands
 where sessions make not sense.

## Repositories

Usually you will use this layer with Doctrine ORM, redis, file system ... In this layer only will manipulate data but never will have business logic.

**Dependencies allowed:**

 - Services (*)
 - Value Objects
 - External libraries

**No dependencies allowed:**

 - ~~Business cases~~
 - ~~Services~~
 - ~~Repositories~~

(*) Only specific service like cache service, log, redis service or something similar.

## Services

In this layer you will encapsulate funcionalities not directly related with logic business (Business cases): send emails,
logs, cache, export files, ...

**Dependencies allowed:**

 - Services
 - Repositories
 - Value Objects
 - External libraries

**No dependencies allowed:**

 - ~~Business cases~~

## Controllers

Methods in controllers should be small and don`t include business logic. Only will have small logic to handle the response but never business logic.

Like general rule only should call in controller business case layer, but some times you will not need business logic in controller,
for example when your controller return a specific object (GET /comment/{id}/rating), in these case is allowed use repositories.

## Value objects

To save values like constants or similar things and not match directly with entity in database. Example: ErrorCode, VoteAction, ...

# Tests and coverage

Like general rule should be tested layers like custom services, business cases.

   - Business cases
   - Custom Services

If you have value objects or components with logic you should test them.

Depends of the project can be tested controllers using functional, commands or repositories with integration tests
but we need evaluate these posibilities in each case, and you must think about costs of integration and functional test (E2E),
usually they have high maintenance and if you are using CI environment operations people will have extra work :-)

In this demo application I did integration test with class RedisRatingRepository because is a repository to handle data with specific
structure of data in redis. Repositories from Doctrine is not necessary to test them.

# Redis benchmark

The repository `RedisRatingRepository` use redis command `sadd` and `srem`.

You can execute command,

`redis-benchmark -t sadd -n 100000 -q`

You will get the next result,

`SADD: 66137.57 requests per second`

That result was got in vagrant machine with Debian, 4096MB and 2 cores.

# Installation

Add http://demo.local like a virtual host in your local machine

- ```composer install```
- ```./app/console doctrine:database:create --env=dev```
- ```./app/console doctrine:schema:create --env=dev```
- ```bash ./app/console doctrine:fixtures:load --fixtures src/AppBundle/DataFixtures/Doctrine/ORM```
- ```php ./app/console api:doc:dump```


# How check code coverage

```./bin/phpunit -c app/ --coverage-text``` or ```./bin/phpunit -c app/ --coverage-html ./build```

# See apidoc rest with sandbox

http://demo.local/app_dev.php/api/doc

# Routing API rest

Get total score comment:

- http://demo.local/app_dev.php/comment/{id}/rating [GET]

Create score comment (UP or DOWN):

- http://demo.local/app_dev.php/comment/{id}/rating [POST]

# Testing API rest in your local machine

You can test API rest in your local using sandbox enabled in API doc or using postman.

## Sandbox enabled in API doc bundle

To test API Rest you can use sandbox enabled in (http://demo.local/app_dev.php/api/doc)

## Postman

Import file `rest.postman_collection.json` in postman application.

## Examples responses

### Get total score, success

`Request`

Method `GET`

```
http://demo.local/app_dev.php/comment/1/rating
```

`Response`

Status `200`

```
5
```

Where 5 is total score to comment ID = 1.


### Get total score, fail

`Request`

Method `GET`

```
http://demo.local/app_dev.php/comment/30/rating
```
Where comment ID = 30 does not exist.

`Response`

Status `404`

```json
{
  "Errors": [
    "Comment does not exist"
  ],
  "ErrorCode": 20,
  "TotalErrors": 1
}
```

### Put score, success

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/1/rating
```

With body:

```
{"userId": 5, "score": -1}
```

`Response`

Status `200`

```json
{
  "Comment": {
    "Id": 1,
    "Text": "Aut debitis natus beatae consectetur ea. Tempora voluptate veniam illum reprehenderit et voluptatibus minima. Facilis numquam voluptate sint. Quos eaque amet quisquam eligendi.\nFacilis ex magnam qui et aliquam. Eum aut nisi excepturi aut non. Magnam delectus nam commodi praesentium sit.",
    "CreatedAt": "1984-05-19T10:55:10+0200",
    "UpdatedAt": "2009-03-16T22:44:51+0100",
    "User": {
      "Id": 1,
      "Name": "Cloyd",
      "CreatedAt": "1978-03-26T22:12:16+0100",
      "UpdatedAt": "1998-05-27T19:39:48+0200"
    }
  },
  "Votes": {
    "2": {
      "UserId": 2,
      "Score": 1
    },
    "5": {
      "UserId": 5,
      "Score": -1
    }
  }
}
```

### Put score, fail 1

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/1/rating
```

With body:

```
{"userId": 2, "score": 10}
```

`Response`

Status `400`

```json
{
  "Errors": {
    "score": "Invalid '10' provided."
  },
  "ErrorCode": 10,
  "TotalErrors": 1
}
```

Only are valid values 1 or -1 to score field.

### Put score, fail 2

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/50/rating
```

With body:

```
{"userId": 2, "score": -1}
```

Where comment ID = 50 does not exist.


`Response`

Status `400`

```json
{
  "Errors": [
    "Comment does not exist"
  ],
  "ErrorCode": 40,
  "TotalErrors": 1
}
```

### Put score, fail 3

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/1/rating
```

With body:

```
{"userId": 2, "score": -1}
```

Where user ID = 2 put score in the past.


`Response`

Status `400`

```json
{
  "Errors": [
    "User 2 already vote"
  ],
  "ErrorCode": 50,
  "TotalErrors": 1
}
```

### Put score, fail 4

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/1/rating
```

With body:

```
{"userId": 1, "score": -1}
```

Where user ID = 1 is the author of comment


`Response`

Status `400`

```json
{
  "Errors": [
    "You can not vote your own comment"
  ],
  "ErrorCode": 50,
  "TotalErrors": 1
}
```

### Put score, fail 5

`Request`

Method `POST`

```
http://demo.local/app_dev.php/comment/1/rating
```

With body:

```
{"userId": 1, "score": -1
```

Where the body is bad json format

`Response`

Status `400`

```json
{
  "Errors": [
    "unexpected end of data, ErrorCode: 4"
  ],
  "ErrorCode": 10,
  "TotalErrors": 1
}
```

# Screenshots

## API doc from nelmio/api-doc-bundle

![Alt text](app/Resources/api_rest.png?raw=true "API doc from nelmio/api-doc-bundle")

## Test coverage

![Alt text](app/Resources/test_coverage.png?raw=true "Test coverage")


# Coding Standard

Symfony2 coding standard, you can get here https://github.com/djoos/Symfony2-codingstandard.git:

And execute command:

```./bin/phpcs --extensions=php --colors --standard=/path/Symfony2-coding-standard_data/Symfony2 ./src```




