<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подписка</title>
</head>
<body>
<h1>Подписка на рассылку</h1>
<form action="{{ route('subscribe') }}" method="POST">
    @csrf
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <button type="submit">Подписаться</button>
</form>
</body>
</html>
