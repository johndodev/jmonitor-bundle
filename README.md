# Jmonitor Bundle

## Installation

```bash
composer require johndodev/jmonitor-bundle:dev-master
```
## Configuration

### .env
```yaml
JMONITOR_API_KEY=your_api_key
```

### config/packages/jmonitor.yaml
```yaml
jmonitor:
    enabled: true
    project_api_key: '%env(JMONITOR_API_KEY)%'
    http_client: 'http_client'
    cache: 'cache.app'
    schedule: 'default'
#    collectors:
#        mysql:
#            type: mysql
```
