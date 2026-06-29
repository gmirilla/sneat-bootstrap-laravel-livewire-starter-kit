update users
set password= (select password from users where id =1)
where id =47