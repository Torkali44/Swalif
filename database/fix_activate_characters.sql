-- تفعيل كل الشخصيات عشان تظهر للاعبين في /profile
UPDATE `characters` SET `is_active` = 1;

-- للتأكد:
-- SELECT id, name_ar, is_active, sort_order FROM characters ORDER BY sort_order, id;
