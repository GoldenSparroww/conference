```powershell
Get-ChildItem -Recurse | Where-Object {$_.FullName -notlike "*\vendor\*" -and $_.FullName -notlike "*\css\*" -and $_.FullName -notlike "*\js\*"}
```

```php
file_put_contents('log_user.txt', print_r($user, true), FILE_APPEND);
file_put_contents('log_SESSION.txt', print_r($_SESSION, true), FILE_APPEND);
```