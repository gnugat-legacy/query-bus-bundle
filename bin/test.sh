#!/usr/bin/env bash

rm -rf Tests/app/cache/* Tests/app/logs/*
vendor/bin/phpunit
