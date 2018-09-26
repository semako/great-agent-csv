# API Documentation

## Usage

### Run in Swagger UI

Run from this dir: 
```
docker run -p 8080:8080 \
	-e SWAGGER_JSON=/app/swagger.yaml \
	-v ${PWD}/shared/:/usr/share/nginx/html/shared/ \
	-v ${PWD}:/app swaggerapi/swagger-ui
```

In browser, head to: [`http://127.0.0.1:8080`](http://127.0.0.1:8080)