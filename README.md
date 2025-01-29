# Jmonitor Bundle

## Installation

```bash
composer require johndodev/jmonitor-bundle
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
    collectors:
        mysql:
            type: mysql
```
