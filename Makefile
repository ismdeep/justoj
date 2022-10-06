test-create:
	docker cp install.sql justoj-db:/install.sql
	docker cp install-db.sh  justoj-db:/install-db.sh
	waitdb -dialect mysql -dsn 'root:123456@tcp(127.0.0.1:3306)/justoj?parseTime=true&loc=Local&charset=utf8mb4,utf8'
	docker exec -d justoj-db bash /install-db.sh
	docker cp nginx-config justoj-web:/etc/nginx/sites-enabled/default
	docker cp install-web.sh justoj-web:/install-web.sh
	docker exec -d justoj-web bash /install-web.sh
	docker exec -d justoj-web nginx -s reload
