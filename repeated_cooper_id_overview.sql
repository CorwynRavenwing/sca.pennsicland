# Connection: pennsic
# Host: db.sca.org
# Saved: 2012-11-26 23:13:50
# 
select penn_number, count(*) as num
from cooper_data
group by penn_number
having num > 1
limit 10
