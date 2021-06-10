test-create:
	docker run --name justoj-db \
	    -e MYSQL_ROOT_PASSWORD=123456 \
	    -e MYSQL_DATABASE=justoj \
	    -p 3306:3306 \
	    -d mysql:5.7 \
	    --character-set-server=utf8mb4 \
	    --collation-server=utf8mb4_unicode_ci
	docker run --name justoj-web \
		--link justoj-db:justoj-db \
		-p 80:80 \
		-v $(CUSDIR):/var/www/justoj \
		-d ismdeep/justoj-web:0.0.13

test-up:
	docker start justoj-db
	docker start justoj-web

test-down:
	docker stop justoj-web
	docker stop justoj-db

test-clean:
	docker rm justoj-web
	docker rm justoj-db
