# 身分認證

------

註冊新帳號

- URL

    /register

- Method

    `POST`

- Data Params

    Paramters depends on environment variable `REGISTER_MODE`.

    - When `REGISTER_MODE` = 'app'
    
        **Required:**

        - uid=[string]
        - email=[string]

    - When `REGISTER_MODE` = 'web'
    
        **Required:**

        - email=[string]
        - nickname=[string]
        - password=[string]
        - ticket_number=[string]

- Success Response

    - Code: 200
    - Content:

    ```json
    {
        "success": true,
        "message": "Success.",
        "data": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVnaXN0ZXIiLCJpYXQiOjE1NjQzMjMyNTksImV4cCI6MTU2NDQ5NjA1OSwibmJmIjoxNTY0MzIzMjU5LCJqdGkiOiJQWGp3RzcyTlNNSW9QNThJIiwic3ViIjoxMywicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOTcxNTNhMTRlMGIwNDc1NDZhYSJ9.WA3ggxqpXCvnThIz4Py6aREk9f4Fl7sXzeobFgbC840",
            "token_type": "bearer",
            "expires_in": 172800
        }
    }
    ```

------

登入

- URL

    /login

- Method

    `POST`

- Data Params

    **Required:**

    - uid=[string]
    - password=[string]

- Success Response

    - Code: 200
    - Content:

    ```json
    {
        "success": true,
        "message": "Success.",
        "data": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVnaXN0ZXIiLCJpYXQiOjE1NjQzMjMyNTksImV4cCI6MTU2NDQ5NjA1OSwibmJmIjoxNTY0MzIzMjU5LCJqdGkiOiJQWGp3RzcyTlNNSW9QNThJIiwic3ViIjoxMywicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOTcxNTNhMTRlMGIwNDc1NDZhYSJ9.WA3ggxqpXCvnThIz4Py6aREk9f4Fl7sXzeobFgbC840",
            "token_type": "bearer",
            "expires_in": 172800
        }
    }
    ```

------

更新 token

- URL

    /refreshToken

- Method

    `GET`

- Success Response

    - Code: 200
    - Content:

    ```json
    {
        "success": true,
        "message": "Success.",
        "data": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVnaXN0ZXIiLCJpYXQiOjE1NjQzMjMyNTksImV4cCI6MTU2NDQ5NjA1OSwibmJmIjoxNTY0MzIzMjU5LCJqdGkiOiJQWGp3RzcyTlNNSW9QNThJIiwic3ViIjoxMywicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOTcxNTNhMTRlMGIwNDc1NDZhYSJ9.WA3ggxqpXCvnThIz4Py6aREk9f4Fl7sXzeobFgbC840",
            "token_type": "bearer",
            "expires_in": 172800
        }
    }
    ```
