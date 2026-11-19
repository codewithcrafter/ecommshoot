import pymysql

conn = pymysql.connect(host='127.0.0.1', port=3307, user='root', password='', db='ecommshoot')
cur = conn.cursor()
new_hours = 'Monday – Friday: 9:00 AM – 6:30 PM IST'
cur.execute("UPDATE website_settings SET setting_value = %s WHERE setting_key = 'site_hours'", (new_hours,))
conn.commit()
print('Rows affected:', cur.rowcount)

cur.execute("SELECT setting_key, setting_value FROM website_settings WHERE setting_key = 'site_hours'")
print('Verified setting:', cur.fetchall())
conn.close()
