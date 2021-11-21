help:
	@sleep 1

test-create:
	@make test-clean >/dev/null 2>&1
	docker run --name justoj-db \
	    -e MYSQL_ROOT_PASSWORD=123456 \
	    -e MYSQL_DATABASE=justoj \
	    -p 3306:3306 \
	    -d hub.deepin.com/library/mysql:8 \
	    --character-set-server=utf8mb4 \
	    --collation-server=utf8mb4_0900_ai_ci
	docker run --name justoj-redis \
		-p 6379:6379 \
		-d redis:latest
	docker cp install.sql justoj-db:/install.sql
	docker cp install-db.sh  justoj-db:/install-db.sh
	waitdb -dialect mysql -dsn 'root:123456@tcp(127.0.0.1:3306)/justoj?parseTime=true&loc=Local&charset=utf8mb4,utf8'
	docker exec -d justoj-db bash /install-db.sh
	docker run --name justoj-web \
		--link justoj-db:justoj-db \
		-p 80:80 \
		-v $(CURDIR):/var/www \
		-d ismdeep/nginx-php:ubuntu-20-04
	docker cp nginx-config justoj-web:/etc/nginx/sites-enabled/default
	docker cp install-web.sh justoj-web:/install-web.sh
	docker exec -d justoj-web bash /install-web.sh
	docker exec -d justoj-web nginx -s reload

test-clean:
	-docker stop justoj-web
	-docker stop justoj-db
	-docker stop justoj-redis
	-docker rm justoj-web
	-docker rm justoj-db
	-docker rm justoj-redis
