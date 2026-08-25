-- تأكيد إن حسابك أدمن (غيّر الإيميل لو مختلف)
-- SELECT id, name, email, is_admin FROM users WHERE name LIKE '%tork%' OR email LIKE '%tork%';

UPDATE `users`
SET `is_admin` = 1
WHERE `name` LIKE '%tork%' OR `email` LIKE '%tork%';

-- لو عاوز تشوفي كل الأدمن:
-- SELECT id, name, email, is_admin FROM users WHERE is_admin = 1;
