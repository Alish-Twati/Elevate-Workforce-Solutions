-- ============================
-- Elevate Workforce Solutions
-- Sample Data (Seed)
-- 
-- @author Alish Twati
-- @date June 2025
-- @version 1.0
-- ============================

-- ============================
-- Insert Categories
-- ============================

INSERT INTO categories (name, description) VALUES
('Information Technology', 'Software development, IT support, networking, and tech-related jobs'),
('Marketing & Sales', 'Marketing, advertising, sales, and business development positions'),
('Finance & Accounting', 'Accounting, finance, banking, and investment opportunities'),
('Healthcare', 'Medical, nursing, pharmaceutical, and healthcare services'),
('Education', 'Teaching, training, tutoring, and educational administration'),
('Engineering', 'Civil, mechanical, electrical, and other engineering fields'),
('Human Resources', 'HR management, recruitment, and employee relations'),
('Customer Service', 'Customer support, service desk, and client relations'),
('Design & Creative', 'Graphic design, UI/UX, content creation, and creative roles'),
('Legal', 'Legal services, paralegal, and law-related positions');

-- ============================
-- Insert Sample Users
-- ============================

-- Admin User (password: admin123)
INSERT INTO users (email, password, user_type, first_name, last_name, phone) VALUES
('admin@elevate. com', '$2y$10$x4dPURPn. nlwfglu7p0OTOTNEeRJw88j6L8BTDk5I9o6h7hrdiemC', 'admin', 'Admin', 'User', '+977-9800000001');

-- Company Users (password: company123) - Will be updated via PHP script
INSERT INTO users (email, password, user_type, first_name, last_name, phone) VALUES
('hr@technepal.com', 'TEMP_HASH', 'company', 'Ramesh', 'Sharma', '+977-9800000002'),
('contact@innovatesoft.com', 'TEMP_HASH', 'company', 'Sita', 'Thapa', '+977-9800000003'),
('jobs@digitalmedia.com', 'TEMP_HASH', 'company', 'Prakash', 'Adhikari', '+977-9800000004'),
('hr@financecorp.com', 'TEMP_HASH', 'company', 'Anita', 'Rai', '+977-9800000005');

-- Job Seeker Users (password: jobseeker123) - Will be updated via PHP script
INSERT INTO users (email, password, user_type, first_name, last_name, phone) VALUES
('john.doe@email.com', 'TEMP_HASH', 'jobseeker', 'John', 'Doe', '+977-9800000006'),
('jane.smith@email.com', 'TEMP_HASH', 'jobseeker', 'Jane', 'Smith', '+977-9800000007'),
('ram.bahadur@email.com', 'TEMP_HASH', 'jobseeker', 'Ram', 'Bahadur', '+977-9800000008'),
('sita.kumari@email.com', 'TEMP_HASH', 'jobseeker', 'Sita', 'Kumari', '+977-9800000009');

-- ============================
-- Insert Companies
-- ============================

INSERT INTO companies (user_id, company_name, description, location, website, industry, company_size, founded_year) VALUES
(2, 'Tech Nepal Pvt.  Ltd.', 'Leading IT solutions provider in Nepal specializing in software development and digital transformation. ', 'Kathmandu, Nepal', 'https://www.technepal.com', 'Information Technology', '51-200', 2015),
(3, 'InnovateSoft Solutions', 'Innovative software development company focused on creating cutting-edge applications and platforms.', 'Lalitpur, Nepal', 'https://www.innovatesoft.com', 'Information Technology', '11-50', 2018),
(4, 'Digital Media Hub', 'Full-service digital marketing agency helping businesses grow their online presence.', 'Pokhara, Nepal', 'https://www.digitalmedia.com', 'Marketing & Sales', '11-50', 2019),
(5, 'Finance Corp Nepal', 'Premier financial services company offering accounting, auditing, and financial consulting. ', 'Kathmandu, Nepal', 'https://www.financecorp.com', 'Finance & Accounting', '201-500', 2010);

-- ============================
-- Insert Sample Jobs
-- ============================

INSERT INTO jobs (company_id, category_id, title, description, requirements, location, salary_min, salary_max, job_type, experience_level, status, deadline) VALUES
(1, 1, 'Senior PHP Developer', 
'We are looking for an experienced PHP Developer to join our dynamic team. The ideal candidate will have strong experience in PHP frameworks, MySQL, and modern web development practices.',
'- 5+ years of PHP development experience
- Strong knowledge of Laravel or CodeIgniter
- Experience with MySQL, Git, and RESTful APIs
- Bachelor''s degree in Computer Science or related field
- Excellent problem-solving skills',
'Kathmandu, Nepal', 60000, 100000, 'full-time', 'senior', 'active', '2026-07-31'),

(1, 1, 'Frontend Developer (React)', 
'Join our team as a Frontend Developer specializing in React.js.  You will be responsible for building responsive and interactive user interfaces.',
'- 3+ years of React.js experience
- Strong HTML, CSS, and JavaScript skills
- Experience with Redux, REST APIs
- Knowledge of responsive design
- Portfolio of previous work required',
'Kathmandu, Nepal', 50000, 80000, 'full-time', 'intermediate', 'active', '2026-08-15'),

(2, 1, 'Mobile App Developer (Flutter)', 
'Seeking a talented Mobile App Developer with Flutter experience to build cross-platform mobile applications.',
'- 2+ years of Flutter development
- Experience with Dart programming
- Knowledge of Firebase and REST APIs
- Published apps on Play Store/App Store
- Strong problem-solving abilities',
'Lalitpur, Nepal', 55000, 85000, 'full-time', 'intermediate', 'active', '2026-07-25'),

(2, 1, 'UI/UX Designer', 
'Creative UI/UX Designer needed to design intuitive and beautiful user experiences for web and mobile applications.',
'- 3+ years of UI/UX design experience
- Proficiency in Figma, Adobe XD, or Sketch
- Strong portfolio demonstrating design skills
- Understanding of user-centered design principles
- Experience with prototyping and wireframing',
'Lalitpur, Nepal', 45000, 70000, 'full-time', 'intermediate', 'active', '2026-08-10'),

(3, 2, 'Digital Marketing Specialist', 
'Looking for a creative Digital Marketing Specialist to develop and execute marketing campaigns across various digital channels.',
'- 2+ years in digital marketing
- Experience with SEO, SEM, and social media marketing
- Knowledge of Google Analytics and Ads
- Strong content creation skills
- Bachelor''s degree in Marketing or related field',
'Pokhara, Nepal', 40000, 65000, 'full-time', 'intermediate', 'active', '2026-07-20'),

(3, 2, 'Content Writer', 
'Talented Content Writer needed to create engaging content for blogs, websites, and social media platforms.',
'- 1-2 years of content writing experience
- Excellent English writing skills
- SEO knowledge is a plus
- Ability to research and write on various topics
- Portfolio of published work',
'Pokhara, Nepal', 30000, 45000, 'full-time', 'entry', 'active', '2026-08-05'),

(4, 3, 'Accountant', 
'Experienced Accountant required to manage financial records, prepare reports, and ensure compliance.',
'- Bachelor''s degree in Accounting or Finance
- 3+ years of accounting experience
- Knowledge of accounting software (Tally, QuickBooks)
- Strong analytical and organizational skills
- CA inter or equivalent certification preferred',
'Kathmandu, Nepal', 45000, 70000, 'full-time', 'intermediate', 'active', '2026-07-28'),

(4, 3, 'Financial Analyst', 'Join our finance team as a Financial Analyst to analyze financial data and provide insights for business decisions.',
'- Bachelor''s degree in Finance or Economics
- 2+ years of financial analysis experience
- Strong Excel and data analysis skills
- Knowledge of financial modeling
- CFA Level 1 or equivalent is a plus',
'Kathmandu, Nepal', 50000, 80000, 'full-time', 'intermediate', 'active', '2026-08-12'),

(1, 1, 'DevOps Engineer', 
'Experienced DevOps Engineer needed to manage infrastructure, automate deployments, and ensure system reliability.',
'- 4+ years of DevOps experience
- Strong knowledge of Docker, Kubernetes
- Experience with AWS or Azure
- Proficiency in Linux and scripting
- CI/CD pipeline experience',
'Kathmandu, Nepal', 70000, 110000, 'full-time', 'senior', 'active', '2026-08-20'),

(2, 1, 'IT Intern', 
'Internship opportunity for students or recent graduates interested in gaining hands-on IT experience.',
'- Currently pursuing or recently completed IT degree
- Basic programming knowledge
- Eagerness to learn
- Good communication skills
- Ability to work in a team',
'Lalitpur, Nepal', 15000, 25000, 'internship', 'entry', 'active', '2026-07-30');

-- ============================
-- Insert Sample Applications
-- ============================

INSERT INTO applications (job_id, user_id, cover_letter, resume, status) VALUES
(1, 6, 'I am writing to express my strong interest in the Senior PHP Developer position.  With over 6 years of experience in PHP development and extensive knowledge of Laravel framework, I believe I would be a valuable addition to your team.', 'resume_john_doe.pdf', 'pending'),
(2, 7, 'I am excited to apply for the Frontend Developer position. My 4 years of experience with React.js and passion for creating beautiful user interfaces make me an ideal candidate for this role.', 'resume_jane_smith.pdf', 'reviewed'),
(3, 8, 'As a Flutter developer with 3 years of experience, I am thrilled about the opportunity to join your mobile development team. I have successfully published multiple apps on both platforms. ', 'resume_ram_bahadur.pdf', 'shortlisted'),
(5, 9, 'I am applying for the Digital Marketing Specialist position. With my background in SEO and social media marketing, I am confident I can help grow your digital presence.', 'resume_sita_kumari. pdf', 'pending'),
(6, 6, 'I would love to join your team as a Content Writer. My passion for storytelling and 2 years of writing experience align perfectly with this opportunity.', 'resume_john_doe_2.pdf', 'reviewed');

-- ============================
-- Success Message
-- ============================

SELECT 'Sample data inserted successfully!' AS message;
SELECT '⚠️  IMPORTANT: Run fix_all_passwords.php to set real password hashes!' AS warning;
SELECT 'Default Admin Login: admin@elevate.com | Password: admin123' AS info;
SELECT 'Company Login:  hr@technepal.com | Password: company123' AS info;
SELECT 'Job Seeker Login: john.doe@email.com | Password: jobseeker123' AS info;