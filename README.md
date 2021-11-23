# TYPO3 CMS Extension "dp_news_import"

This extension provides a json import schedule for `EXT:News`

## json format for import

```json


{
  "data":[
    {
      "ref_id": "UNIQUE_ID",
      "feed": "CATEGORY",
      "title": "HEADLINE",
      "teaser": "TEASER_TEXT",
      "detail": "BODY_TEXT",
      "media_url": "MEDUA_IMAGE_URL",
      "created_at": "YYYY-MM-dd H:i:s",
      "url": "EXTERNAL_LINK",
    },
    ....
  ]
}
```