test-create:
	docker run --name justoj-db \
	    -e MYSQL_ROOT_PASSWORD=123456 \
	    -e MYSQL_DATABASE=justoj \
	    -p 3306:3306 \
	    -d mysql:8 \
	    --character-set-server=utf8mb4 \
	    --collation-server=utf8mb4_0900_ai_ci
	docker cp install.sql justoj-db:/install.sql
	docker cp install-db.sh  justoj-db:/install-db.sh
	sleep 50
	docker exec -d justoj-db bash /install-db.sh
	docker run --name justoj-web \
		--link justoj-db:justoj-db \
		-p 80:80 \
		-v $(CURDIR):/var/www/justoj \
		-d ismdeep/nginx-php:ubuntu-20-04
	docker cp nginx-config justoj-web:/etc/nginx/sites-enabled/justoj
	docker cp install-web.sh justoj-web:/install-web.sh
	docker exec -d justoj-web bash /install-web.sh
	docker exec -d justoj-web nginx -s reload

test-up:
	docker start justoj-db
	docker start justoj-web

test-down:
	-docker stop justoj-web
	-docker stop justoj-db

test-clean: test-down
	-docker rm justoj-web
	-docker rm justoj-db
