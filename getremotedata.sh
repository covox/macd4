ssh jw@admin.istand.tv "mysqldump --add-drop-table -uSpartacus -pholo3601q2w3e polo > poloremote.sql"

ssh jw@admin.istand.tv "zip poloremote.zip poloremote.sql"

scp jw@admin.istand.tv:poloremote.zip ./

unzip -o poloremote.zip

mysql -uroot polo < poloremote.sql


