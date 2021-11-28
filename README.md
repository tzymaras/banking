# setup
```
git clone https://github.com/tzymaras/banking.git
make docker-build-dev
```
## run migrations
```
make docker-run-migrations
```
# usage
## view logs
```
make docker-logs-dev
```
### Register a new user
```
http://localhost:8080/register
```
### Account overview
```
http://localhost:8080/banking/account
```
