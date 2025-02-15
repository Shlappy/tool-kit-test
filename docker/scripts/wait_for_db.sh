#!/usr/bin/env bash

while !</dev/tcp/db/5432
do
    echo "Ожидание готовности PostgreSQL, подождите немного..."
    sleep 2;
done
echo "PostgreSQL готов"
