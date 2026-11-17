-- قاعدة بيانات مشروع IT1515: إدارة المقررات والتسجيل
-- يمكن تنفيذ الملف مباشرة؛ ينشئ قاعدة coursehub والجداول والبيانات التجريبية.
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS coursehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coursehub;

CREATE TABLE IF NOT EXISTS departments (
    department_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS courses (
    course_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    credit_hours TINYINT UNSIGNED NOT NULL DEFAULT 3,
    department_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_department FOREIGN KEY (department_id)
        REFERENCES departments(department_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    enrollment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    CONSTRAINT uq_student_course UNIQUE (user_id, course_id),
    CONSTRAINT fk_enrollments_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id)
        REFERENCES courses(course_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- بيانات تجريبية: كلمة مرور المسؤول Admin123! وكلمة مرور الطلاب Student123!
-- تُخزن كلمات المرور كـ hashes وليست كنص صريح.
INSERT INTO departments (department_id, department_name, description) VALUES
(1, 'علوم الحاسب', 'برامج ومقررات الحوسبة والبرمجة.'),
(2, 'تقنية المعلومات', 'مقررات الشبكات والأنظمة وتقنية المعلومات.'),
(3, 'نظم المعلومات', 'تحليل وتصميم نظم المعلومات.'),
(4, 'الرياضيات', 'مقررات الرياضيات والإحصاء.'),
(5, 'اللغة الإنجليزية', 'مهارات اللغة الإنجليزية الأكاديمية.'),
(6, 'إدارة الأعمال', 'مقررات الإدارة والأعمال.'),
(7, 'الأمن السيبراني', 'مبادئ حماية الأنظمة والبيانات.'),
(8, 'هندسة البرمجيات', 'تحليل وتطوير البرمجيات.'),
(9, 'الذكاء الاصطناعي', 'مقررات الذكاء الاصطناعي وعلوم البيانات.'),
(10, 'الفيزياء', 'مقررات الفيزياء الأساسية.')
ON DUPLICATE KEY UPDATE department_name = VALUES(department_name);

INSERT INTO users (user_id, full_name, email, password_hash, role) VALUES
(1, 'مسؤول النظام', 'admin@example.edu', '$2y$10$GEY8yDFN/M5dBBUtiP4IvuwD2T95jh26u2TGzsR5uUy6u5OvJR5Ke', 'admin'),
(2, 'سارة أحمد', 'student01@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(3, 'محمد خالد', 'student02@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(4, 'نورة علي', 'student03@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(5, 'فيصل حسن', 'student04@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(6, 'ريم سعد', 'student05@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(7, 'عبدالله صالح', 'student06@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(8, 'لينا فهد', 'student07@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(9, 'عمر طلال', 'student08@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(10, 'هيا فواز', 'student09@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student'),
(11, 'تركي ناصر', 'student10@example.edu', '$2y$10$zWincCCtzSYSchd5uGKBz.PLDCfxGZytCF/ytAPU6fj2WXuudzs7S', 'student')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO courses (course_id, course_code, course_name, description, credit_hours, department_id) VALUES
(1, 'IT1515', 'تقنيات الويب 2', 'تطبيقات ويب ديناميكية باستخدام PHP وقواعد البيانات.', 3, 2),
(2, 'CS101', 'مقدمة في علوم الحاسب', 'مفاهيم أساسية في الحوسبة وحل المشكلات.', 3, 1),
(3, 'CS202', 'هياكل البيانات', 'طرق تنظيم البيانات والخوارزميات الأساسية.', 3, 1),
(4, 'IT220', 'أساسيات قواعد البيانات', 'تصميم قواعد البيانات والاستعلام عنها باستخدام SQL.', 3, 2),
(5, 'IS210', 'تحليل النظم', 'مقدمة في تحليل الأنظمة وتوثيق المتطلبات.', 3, 3),
(6, 'MATH120', 'رياضيات متقطعة', 'مفاهيم المنطق والمجموعات والعلاقات.', 3, 4),
(7, 'CYB201', 'مبادئ الأمن السيبراني', 'مقدمة في حماية البيانات والأنظمة.', 3, 7),
(8, 'SE301', 'هندسة البرمجيات', 'مراحل بناء البرمجيات والعمل ضمن فريق.', 3, 8),
(9, 'AI210', 'مدخل إلى الذكاء الاصطناعي', 'مفاهيم أولية في الذكاء الاصطناعي وتطبيقاته.', 3, 9),
(10, 'ENG101', 'الإنجليزية الأكاديمية', 'مهارات القراءة والكتابة في السياق الأكاديمي.', 2, 5)
ON DUPLICATE KEY UPDATE course_code = VALUES(course_code);

INSERT INTO enrollments (enrollment_id, user_id, course_id, status) VALUES
(1, 2, 1, 'active'), (2, 2, 4, 'active'), (3, 3, 2, 'active'),
(4, 3, 3, 'active'), (5, 4, 1, 'active'), (6, 4, 5, 'active'),
(7, 5, 7, 'active'), (8, 5, 8, 'active'), (9, 6, 9, 'active'),
(10, 6, 10, 'active'), (11, 7, 2, 'active'), (12, 7, 4, 'active'),
(13, 8, 3, 'active'), (14, 8, 8, 'active'), (15, 9, 5, 'active'),
(16, 9, 6, 'active'), (17, 10, 7, 'active'), (18, 10, 9, 'active'),
(19, 11, 1, 'active'), (20, 11, 10, 'active')
ON DUPLICATE KEY UPDATE enrollment_id = VALUES(enrollment_id);
