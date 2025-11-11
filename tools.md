```powershell
Get-ChildItem -Recurse | Where-Object {$_.FullName -notlike "*\vendor\*" -and $_.FullName -notlike "*\css\*" -and $_.FullName -notlike "*\js\*"}
```