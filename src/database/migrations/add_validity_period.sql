-- 添加用户账号有效期字段
ALTER TABLE users ADD COLUMN valid_from DATE NULL;
ALTER TABLE users ADD COLUMN valid_until DATE NULL;

-- 更新现有用户,设置为永久有效
UPDATE users SET valid_from = NULL, valid_until = NULL; 