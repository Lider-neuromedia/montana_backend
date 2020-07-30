<form action="" method="POST">
    {{ csrf_field() }}
    <select name="rol_id" id="">
        <option value="1">administrador</option>
        <option value="2">vendedor</option>
        <option value="3">cliente</option>
    </select>
    <input type="text" name="name" placeholder="nombre">
    <input type="email" name="email" placeholder="email">
    <input type="password" name="password" placeholder="password">
</form>