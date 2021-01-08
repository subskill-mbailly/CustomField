# customfield

## Différents types disponibles :
- text
- textarea
- file
- color
- password

## Objet des tabs
```json
{
    "key": "general",                                           # Ref de la tab
    "title": "Général",                                         # Nom propre de la tab
    "icon": "icon-cogs",                                        # Icon
    "fields": []                                                # Champs
}
```

## Objet des fields
```json
{
    "key": "general",
    "title": "Général",
    "icon": "icon-cogs",
    "fields": [
        {
            "key": "contact_email",                             # Ref du champ
            "label": "Email",                                   # Label affiché avant le champ
            "type": "text",                                     # Type de champ
            "instructions": "",                                 # Tooltip d'aide                    (optionel)
            "required": false,                                  # Requis                            (optionel)
            "disabled": false,                                  # Désactivé                         (optionel)
            "maxlength": 200,                                   # Maximum de caractères             (optionel)
            "wrapper": {
                "class": "",                                    # Classe(s)                         (optionel)
                "id": ""                                        # Identifiant                       (optionel)
            },
            "default_value": "",                                # Valeur par défaut                 (optionel)
            "placeholder": "Indiquez votre email de contact",   # Placeholder                       (optionel)
            "suffix": "",                                       # Placeholder                       (optionel)
            "prefix": "",                                       # Placeholder                       (optionel)
        }
    ]
}
```