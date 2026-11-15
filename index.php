
<?php
require_once __DIR__ . "/db.php";

// Check administrator login
$isAdmin = !empty($_SESSION["admin"]);

// Load only published CV documents
$publicCvs = [];
$cvError = false;

try {
    $stmt = $pdo->prepare(
        "SELECT id, original_name, uploaded_at
         FROM cv_uploads
         WHERE is_public = 1
         ORDER BY uploaded_at DESC, id DESC"
    );

    $stmt->execute();
    $publicCvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("CV retrieval error: " . $e->getMessage());
    $cvError = true;
}

// Safe HTML output
if (!function_exists("e")) {
    function e($value) {
        return htmlspecialchars(
            (string)($value ?? ""),
            ENT_QUOTES,
            "UTF-8"
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tadege Yilkal | Full-Stack Software Engineer</title>

    <meta name="description"
          content="Tadege Yilkal's Computer Science portfolio featuring PHP, MySQL, Python, software development and machine learning projects.">

    <meta name="theme-color" content="#020617">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
          rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
        }

        body {
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            background: #020617;
        }

        ::selection {
            background: #6366f1;
            color: white;
        }

        .separated-header {
            background: rgba(2, 6, 23, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.18);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.25);
        }

        .header-avatar-ring {
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.55);
        }

        .nav-link {
            position: relative;
            transition: 0.3s ease;
        }

        .nav-link:hover {
            color: #a5b4fc;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 0;
            height: 2px;
            border-radius: 10px;
            background: linear-gradient(90deg, #6366f1, #ec4899);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-rgb {
            background: linear-gradient(100deg, #6366f1, #9333ea, #ec4899);
            background-size: 200% auto;
            border: 1px solid rgba(255,255,255,0.12);
            color: white;
            box-shadow: 0 5px 25px rgba(99, 102, 241, 0.25);
            transition: all 0.35s ease;
            cursor: pointer;
        }

        .btn-rgb:hover {
            background-position: right center;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(147, 51, 234, 0.35);
        }

        .btn-rgb:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .glass-card {
            background: linear-gradient(
                145deg,
                rgba(30, 41, 59, 0.78),
                rgba(15, 23, 42, 0.72)
            );
            border: 1px solid rgba(148, 163, 184, 0.13);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.35s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(129, 140, 248, 0.45);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.22);
        }

        .section-heading {
            background: linear-gradient(
                90deg,
                #818cf8,
                #c084fc,
                #f0abfc
            );
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-glow {
            position: absolute;
            width: 420px;
            height: 420px;
            background: rgba(99, 102, 241, 0.15);
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
        }

        .profile-glow {
            position: relative;
        }

        .profile-glow::before {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: linear-gradient(
                135deg,
                #6366f1,
                #a855f7,
                #ec4899
            );
            filter: blur(15px);
            opacity: 0.55;
        }

        .profile-glow img {
            position: relative;
            z-index: 1;
        }

        .skill-icon {
            background: linear-gradient(
                135deg,
                rgba(99, 102, 241, 0.2),
                rgba(168, 85, 247, 0.15)
            );
            border: 1px solid rgba(129, 140, 248, 0.15);
        }

        input,
        textarea {
            transition: all 0.25s ease;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .project-tag {
            background: rgba(99, 102, 241, 0.13);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: #c7d2fe;
        }

        .cv-action {
            transition: all 0.25s ease;
        }

        .cv-action:hover {
            transform: translateY(-2px);
        }

        #mobile-menu {
            transition: all 0.3s ease;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #020617;
        }

        ::-webkit-scrollbar-thumb {
            background: #4338ca;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6366f1;
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 2.6rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 antialiased">

<!-- HEADER -->

<header class="fixed top-0 left-0 w-full z-50 separated-header">

    <div class="max-w-7xl mx-auto px-6 py-3.5 flex justify-between items-center">

        <a href="#hero" class="flex items-center gap-3">

            <div class="relative">
                <img src="download.jpg"
                     alt="Tadege Yilkal"
                     class="w-11 h-11 rounded-full object-cover border-2 border-indigo-400 header-avatar-ring">

                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-slate-950 rounded-full"></span>
            </div>

            <div class="text-xl font-extrabold tracking-tight">
                <span class="text-indigo-400">Tadege</span>
                <span class="text-white">Yilkal</span>
            </div>

        </a>

        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-300">
            <a href="#about" class="nav-link">About</a>
            <a href="#skills" class="nav-link">Skills</a>
            <a href="#projects" class="nav-link">Projects</a>
            <a href="#cv" class="nav-link">CV</a>
            <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <div class="flex items-center gap-3">

            <a href="#contact"
               class="hidden sm:flex btn-rgb px-5 py-2.5 rounded-xl text-sm font-semibold items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                Get in Touch
            </a>

            <?php if ($isAdmin): ?>

                <a href="logout.php"
                   onclick="return confirm('Are you sure you want to logout?');"
                   class="hidden sm:flex logout-btn px-4 py-2.5 rounded-xl text-sm font-semibold items-center gap-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </a>

            <?php endif; ?>

            <button id="menu-btn"
                    type="button"
                    class="md:hidden text-slate-300 hover:text-white p-2"
                    aria-label="Toggle navigation"
                    aria-expanded="false">
                <i id="menu-icon" class="fa-solid fa-bars text-xl"></i>
            </button>

        </div>

    </div>

    <!-- MOBILE MENU -->

    <div id="mobile-menu"
         class="hidden md:hidden bg-slate-900/95 border-t border-indigo-500/20 px-6 py-5 space-y-4">

        <a href="#about" class="mobile-link block text-slate-300 hover:text-indigo-300">About</a>
        <a href="#skills" class="mobile-link block text-slate-300 hover:text-indigo-300">Skills</a>
        <a href="#projects" class="mobile-link block text-slate-300 hover:text-indigo-300">Projects</a>
        <a href="#cv" class="mobile-link block text-slate-300 hover:text-indigo-300">CV</a>
        <a href="#contact" class="mobile-link block text-slate-300 hover:text-indigo-300">Contact</a>

        <a href="#contact"
           class="mobile-link block text-center btn-rgb py-3 rounded-xl font-semibold">
            Get in Touch
        </a>

        <?php if ($isAdmin): ?>

            <a href="google.com"
               onclick="return confirm('Are you sure you want to logout?');"
               class="block text-center logout-btn py-3 rounded-xl font-semibold">
                <i class="fa-solid fa-right-from-bracket mr-2"></i>
                Logout
            </a>

        <?php endif; ?>

    </div>

</header>


<!-- HERO SECTION -->

<section id="hero"
         class="min-h-screen flex items-center justify-center pt-32 pb-20 px-6 relative overflow-hidden">

    <div class="hero-glow top-20 left-1/2 -translate-x-1/2"></div>

    <div class="max-w-5xl mx-auto text-center relative z-10">

        <div class="mb-9 flex justify-center">
            <div class="profile-glow">
                <img src="download.jpg"
                     alt="Tadege Yilkal profile"
                     class="w-40 h-40 sm:w-48 sm:h-48 rounded-full object-cover border-4 border-slate-900 shadow-2xl">
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-950/70 border border-indigo-800/60 mb-7 text-indigo-300 text-xs sm:text-sm font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Computer Science Student | Full-Stack Developer
        </div>

        <h1 class="hero-title text-4xl sm:text-7xl font-black tracking-tight mb-6">
            Hello, I'm
            <span class="section-heading">Tadege Yilkal</span>
        </h1>

        <p class="text-xl sm:text-2xl text-slate-300 font-semibold mb-5">
            Full-Stack Developer & Software Engineer
        </p>

        <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed mb-10">
            I build responsive web applications, database-driven systems,
            and practical software solutions using modern technologies.
            I enjoy solving real-world problems through programming.
        </p>

        <div class="flex flex-wrap justify-center gap-4">

            <a href="#projects"
               class="btn-rgb px-7 py-3.5 rounded-xl font-bold flex items-center gap-2">
                Explore My Projects
                <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="#contact"
               class="bg-slate-900 hover:bg-slate-800 border border-slate-700 px-7 py-3.5 rounded-xl font-bold flex items-center gap-2 transition">
                <i class="fa-solid fa-envelope"></i>
                Contact Me
            </a>

        </div>

        <div class="flex justify-center gap-6 mt-10 text-slate-400 text-xl">

            <a href="mailto:tadegeyilkal@gmail.com"
               aria-label="Email"
               class="hover:text-indigo-400 transition">
                <i class="fa-solid fa-envelope"></i>
            </a>

            <a href="https://t.me/tadege123"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="Telegram"
               class="hover:text-indigo-400 transition">
                <i class="fa-brands fa-telegram"></i>
            </a>

            <a href="tel:0928293370"
               aria-label="Phone"
               class="hover:text-indigo-400 transition">
                <i class="fa-solid fa-phone"></i>
            </a>

        </div>

    </div>

</section>


<!-- ABOUT SECTION -->

<section id="about"
         class="py-24 px-6 bg-slate-900/40 border-t border-slate-800">

    <div class="max-w-5xl mx-auto">

        <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-4">
            About Me
        </h2>

        <p class="text-slate-400 text-center max-w-xl mx-auto mb-12">
            Turning ideas and technical knowledge into practical software solutions.
        </p>

        <div class="glass-card rounded-3xl p-8 sm:p-12 shadow-2xl">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">

                <div class="md:col-span-4 flex justify-center">
                    <img src="download.jpg"
                         alt="Tadege Yilkal"
                         class="w-48 h-48 rounded-2xl object-cover border border-slate-700 shadow-xl">
                </div>

                <div class="md:col-span-8">

                    <h3 class="text-2xl font-bold mb-4">
                        Full-Stack Developer & Computer Science Student
                    </h3>

                    <p class="text-slate-300 leading-relaxed mb-4">
                        I am a Computer Science undergraduate student at
                        Injibara University, College of Engineering and
                        Technology. My interests include software development,
                        database systems, web technologies, and computer science.
                    </p>

                    <p class="text-slate-300 leading-relaxed">
                        I work with PHP, MySQL, JavaScript, HTML, CSS,
                        and Python. I enjoy developing useful applications,
                        designing database structures, and solving technical
                        challenges through clean and organized code.
                    </p>

                </div>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-8 border-t border-slate-800">

                <div class="bg-slate-900/70 p-5 rounded-xl border border-slate-800 flex items-center gap-4">
                    <i class="fa-solid fa-layer-group text-indigo-400 text-2xl"></i>
                    <div>
                        <p class="text-xs text-slate-400 uppercase">Specialization</p>
                        <p class="font-bold">Full-Stack Development</p>
                    </div>
                </div>

                <div class="bg-slate-900/70 p-5 rounded-xl border border-slate-800 flex items-center gap-4">
                    <i class="fa-solid fa-graduation-cap text-indigo-400 text-2xl"></i>
                    <div>
                        <p class="text-xs text-slate-400 uppercase">Education</p>
                        <p class="font-bold">B.Sc. Computer Science</p>
                    </div>
                </div>

                <div class="bg-slate-900/70 p-5 rounded-xl border border-slate-800 flex items-center gap-4">
                    <i class="fa-solid fa-code text-indigo-400 text-2xl"></i>
                    <div>
                        <p class="text-xs text-slate-400 uppercase">Main Focus</p>
                        <p class="font-bold">Web Systems & Python</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- SKILLS SECTION -->

<section id="skills" class="py-24 px-6 border-t border-slate-800">

    <div class="max-w-6xl mx-auto">

        <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-4">
            Technical Expertise
        </h2>

        <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">
            Technologies and tools I use to build software applications.
        </p>

        <?php
        $skillGroups = [
            "Frontend Development" => [
                "HTML5 & Semantic Markup",
                "CSS3 & Tailwind CSS",
                "JavaScript (ES6+)",
                "Responsive Web Design",
                "Interactive UI/UX"
            ],

            "Backend & Databases" => [
                "PHP Programming",
                "MySQL Database Design",
                "Relational ER Diagrams",
                "Apache / MAMP",
                "CRUD Operations & Security"
            ],

            "Python & Data Science" => [
                "Python Programming",
                "Pandas & Scikit-Learn",
                "Seaborn & Matplotlib",
                "K-Nearest Neighbors",
                "Data Preprocessing"
            ],

            "Core CS & Systems" => [
                "Data Structures & Algorithms",
                "Operating Systems",
                "8086 Assembly Language",
                "UML Systems Architecture",
                "Networking & Security Basics"
            ]
        ];
        ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <?php foreach ($skillGroups as $heading => $skills): ?>

                <div class="glass-card rounded-2xl p-6">

                    <div class="skill-icon w-14 h-14 rounded-xl flex items-center justify-center text-indigo-400 mb-6">
                        <i class="fa-solid fa-code text-xl"></i>
                    </div>

                    <h3 class="font-bold text-lg mb-5">
                        <?= e($heading) ?>
                    </h3>

                    <ul class="space-y-3 text-sm text-slate-300">

                        <?php foreach ($skills as $skill): ?>

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-indigo-400 mt-1"></i>
                                <span><?= e($skill) ?></span>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<!-- PROJECTS SECTION -->

<section id="projects"
         class="py-24 px-6 bg-slate-900/40 border-t border-slate-800">

    <div class="max-w-6xl mx-auto">

        <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-4">
            My Projects
        </h2>

        <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">
            Explore my deployed applications and planned development projects.
        </p>

        <div class="mb-20">

            <div class="flex items-center gap-4 mb-8">

                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-cloud-arrow-up text-emerald-400 text-xl"></i>
                </div>

                <div>
                    <h3 class="text-2xl font-bold">Deployed Projects</h3>
                    <p class="text-sm text-slate-400">
                        Completed applications available online.
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- PROJECT 1 -->

                <div class="glass-card rounded-2xl p-8">

                    <div class="flex items-center justify-between mb-6">

                        <div class="skill-icon w-14 h-14 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap text-indigo-400 text-2xl"></i>
                        </div>

                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-2 rounded-full">
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            DEPLOYED
                        </span>

                    </div>

                    <h3 class="text-2xl font-bold mb-4">
                        Student Information Management System
                    </h3>

                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                        A web-based academic management system developed
                        for Injibara University. It supports student
                        information management, authentication, academic
                        records, course management, and database operations.
                    </p>

                    <div class="flex flex-wrap gap-2 mb-7">
                        <span class="project-tag text-xs px-3 py-1 rounded-md">PHP</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">MySQL</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">JavaScript</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">HTML/CSS</span>
                    </div>

                    <a href="https://tadegesimsinu.onrender.com"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-rgb inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm w-full">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        View Deployed Project
                    </a>

                </div>

                <!-- PROJECT 2 -->

                <div class="glass-card rounded-2xl p-8">

                    <div class="flex items-center justify-between mb-6">

                        <div class="skill-icon w-14 h-14 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-school text-indigo-400 text-2xl"></i>
                        </div>

                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-2 rounded-full">
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            DEPLOYED
                        </span>

                    </div>

                    <h3 class="text-2xl font-bold mb-4">
                        School Management System
                    </h3>

                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                        A web-based school management application designed
                        for secondary schools. It focuses on managing
                        student information, academic records, grades,
                        teachers, and school activities for Grades 9–12.
                    </p>

                    <div class="flex flex-wrap gap-2 mb-7">
                        <span class="project-tag text-xs px-3 py-1 rounded-md">PHP</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">MySQL</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">JavaScript</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">Bootstrap</span>
                    </div>

                    <a href="https://tadegeedu.onrender.com"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-rgb inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm w-full">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        View Deployed Project
                    </a>

                </div>

            </div>

        </div>

        <!-- FUTURE PROJECTS -->

        <div>

            <div class="flex items-center gap-4 mb-8">

                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-lightbulb text-purple-400 text-xl"></i>
                </div>

                <div>
                    <h3 class="text-2xl font-bold">Future Projects</h3>
                    <p class="text-sm text-slate-400">
                        Planned projects and upcoming development work.
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <div class="glass-card rounded-2xl p-8">

                    <div class="flex items-center justify-between mb-6">

                        <div class="skill-icon w-14 h-14 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-brain text-purple-400 text-2xl"></i>
                        </div>

                        <span class="text-xs font-bold text-purple-300 bg-purple-500/10 px-3 py-2 rounded-full">
                            <i class="fa-solid fa-clock mr-1"></i>
                            PLANNED
                        </span>

                    </div>

                    <h3 class="text-2xl font-bold mb-4">
                        Machine Learning & KNN Classifier
                    </h3>

                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                        A planned machine learning project focused on
                        data cleaning, preprocessing, feature scaling,
                        visualization, and classification using the
                        K-Nearest Neighbors algorithm.
                    </p>

                    <div class="flex flex-wrap gap-2 mb-7">
                        <span class="project-tag text-xs px-3 py-1 rounded-md">Python</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">Pandas</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">Scikit-Learn</span>
                        <span class="project-tag text-xs px-3 py-1 rounded-md">Matplotlib</span>
                    </div>

                    <button type="button"
                            disabled
                            class="w-full bg-slate-800 text-slate-400 px-6 py-3 rounded-xl font-semibold text-sm cursor-not-allowed">
                        <i class="fa-solid fa-hourglass-half mr-2"></i>
                        Coming Soon
                    </button>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- CV SECTION -->

<section id="cv"
         class="py-24 px-6 border-t border-slate-800">

    <div class="max-w-5xl mx-auto">

        <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-4">
            My CV
        </h2>

        <p class="text-slate-400 text-center max-w-xl mx-auto mb-12">
            View or download my published professional CV documents.
        </p>

        <div class="max-w-2xl mx-auto space-y-5">

            <?php if ($cvError): ?>

                <div class="glass-card rounded-2xl p-8 text-center">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400 text-4xl mb-4"></i>
                    <p class="text-slate-300">
                        CV documents are temporarily unavailable.
                        Please try again later.
                    </p>
                </div>

            <?php elseif (empty($publicCvs)): ?>

                <div class="glass-card rounded-2xl p-10 text-center">

                    <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 flex items-center justify-center mx-auto mb-5">
                        <i class="fa-solid fa-file-pdf text-4xl text-indigo-400"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-2">
                        No CV Published Yet
                    </h3>

                    <p class="text-slate-400 text-sm">
                        My professional CV will appear here when it is published.
                    </p>

                </div>

            <?php else: ?>

                <?php foreach ($publicCvs as $cv): ?>

                    <?php
                    $title = $cv["original_name"] ?? "CV Document";

                    $extension = strtolower(
                        pathinfo($title, PATHINFO_EXTENSION)
                    );

                    $icon = $extension === "pdf"
                        ? "fa-file-pdf text-red-400"
                        : "fa-file-word text-blue-400";
                    ?>

                    <div class="glass-card rounded-2xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5">

                        <div class="flex items-center gap-4 min-w-0">

                            <div class="w-14 h-14 shrink-0 rounded-xl bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid <?= e($icon) ?> text-3xl"></i>
                            </div>

                            <div class="min-w-0">

                                <h3 class="font-bold text-slate-100 break-words">
                                    <?= e($title) ?>
                                </h3>

                                <p class="text-xs text-slate-400 mt-2">
                                    <i class="fa-regular fa-calendar mr-1"></i>
                                    Uploaded:
                                    <?= e($cv["uploaded_at"] ?? "") ?>
                                </p>

                                <p class="text-xs text-emerald-400 mt-1">
                                    <i class="fa-solid fa-circle-check mr-1"></i>
                                    Published
                                </p>

                            </div>

                        </div>

                        <div class="flex gap-2 shrink-0">

                            <a href="cv_file.php?id=<?= (int)$cv["id"] ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="cv-action bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 rounded-lg text-sm font-semibold">
                                <i class="fa-solid fa-eye mr-1"></i>
                                View
                            </a>

                            <a href="cv_file.php?id=<?= (int)$cv["id"] ?>&download=1"
                               class="cv-action bg-slate-800 hover:bg-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold">
                                <i class="fa-solid fa-download mr-1"></i>
                                Download
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- CONTACT SECTION -->

<section id="contact"
         class="py-24 px-6 bg-slate-900/40 border-t border-slate-800">

    <div class="max-w-5xl mx-auto">

        <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-4">
            Get In Touch
        </h2>

        <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">
            I am open to software development projects,
            collaborations, and professional opportunities.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- CONTACT INFORMATION -->

            <div class="lg:col-span-5 space-y-5">

                <a href="tel:0928293370"
                   class="glass-card p-6 rounded-2xl flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-phone text-indigo-400 text-xl"></i>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Phone</span>
                        <span class="text-slate-200 font-semibold">0928293370</span>
                    </div>

                </a>

                <a href="https://t.me/tadege123"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="glass-card p-6 rounded-2xl flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                        <i class="fa-brands fa-telegram text-indigo-400 text-xl"></i>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">Telegram</span>
                        <span class="text-slate-200 font-semibold">@tadege123</span>
                    </div>

                </a>

                <a href="mailto:tadegeyilkal@gmail.com"
                   class="glass-card p-6 rounded-2xl flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-envelope text-indigo-400 text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <span class="text-xs text-slate-400 block">Email</span>
                        <span class="text-slate-200 font-semibold text-sm break-all">
                            tadegeyilkal@gmail.com
                        </span>
                    </div>

                </a>

            </div>


            <!-- CONTACT FORM -->

            <div class="lg:col-span-7 glass-card p-8 rounded-2xl">

                <h3 class="text-xl font-bold mb-6">
                    Send Me a Message
                </h3>

                <form id="contactForm"
                      action="send_message.php"
                      method="POST"
                      class="space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label for="contact-name"
                                   class="block text-xs text-slate-400 mb-2">
                                Your Name
                            </label>

                            <input id="contact-name"
                                   type="text"
                                   name="name"
                                   maxlength="100"
                                   required
                                   autocomplete="name"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm">
                        </div>

                        <div>
                            <label for="contact-email"
                                   class="block text-xs text-slate-400 mb-2">
                                Your Email
                            </label>

                            <input id="contact-email"
                                   type="email"
                                   name="email"
                                   maxlength="190"
                                   required
                                   autocomplete="email"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm">
                        </div>

                    </div>

                    <div>
                        <label for="contact-subject"
                               class="block text-xs text-slate-400 mb-2">
                            Subject
                        </label>

                        <input id="contact-subject"
                               type="text"
                               name="subject"
                               maxlength="200"
                               required
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label for="contact-message"
                               class="block text-xs text-slate-400 mb-2">
                            Message
                        </label>

                        <textarea id="contact-message"
                                  name="message"
                                  rows="5"
                                  maxlength="5000"
                                  required
                                  placeholder="Write your message here..."
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full btn-rgb font-bold py-3.5 rounded-xl">

                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Send Message

                    </button>

                    <p id="contactStatus"
                       class="text-sm text-center"
                       aria-live="polite"></p>

                </form>

            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

<footer class="py-10 px-6 border-t border-slate-800 text-center">

    <div class="max-w-5xl mx-auto">

        <div class="text-2xl font-extrabold mb-3">
            <span class="text-indigo-400">Tadege</span>
            <span class="text-white">Yilkal</span>
        </div>

        <p class="text-slate-400 text-sm mb-5">
            Full-Stack Developer | Computer Science Student
        </p>

        <div class="flex justify-center gap-6 text-xl text-slate-400 mb-6">

            <a href="mailto:tadegeyilkal@gmail.com"
               class="hover:text-indigo-400 transition"
               aria-label="Email">
                <i class="fa-solid fa-envelope"></i>
            </a>

            <a href="https://t.me/tadege123"
               target="_blank"
               rel="noopener noreferrer"
               class="hover:text-indigo-400 transition"
               aria-label="Telegram">
                <i class="fa-brands fa-telegram"></i>
            </a>

            <a href="tel:0928293370"
               class="hover:text-indigo-400 transition"
               aria-label="Phone">
                <i class="fa-solid fa-phone"></i>
            </a>

        </div>

        <p class="text-xs text-slate-500">
            &copy; <?= date("Y") ?> Tadege Yilkal. All Rights Reserved.
        </p>

        <p class="text-xs text-slate-600 mt-2">
            Built with PHP, MySQL, Tailwind CSS and JavaScript.
        </p>

    </div>

</footer>


<!-- JAVASCRIPT -->

<script>

// MOBILE NAVIGATION

const menuBtn = document.getElementById("menu-btn");
const mobileMenu = document.getElementById("mobile-menu");
const menuIcon = document.getElementById("menu-icon");

if (menuBtn && mobileMenu && menuIcon) {

    menuBtn.addEventListener("click", function () {

        mobileMenu.classList.toggle("hidden");

        const isOpen = !mobileMenu.classList.contains("hidden");

        menuIcon.className = isOpen
            ? "fa-solid fa-xmark text-xl"
            : "fa-solid fa-bars text-xl";

        menuBtn.setAttribute("aria-expanded", String(isOpen));

    });

    document.querySelectorAll(".mobile-link").forEach(link => {

        link.addEventListener("click", function () {

            mobileMenu.classList.add("hidden");

            menuIcon.className = "fa-solid fa-bars text-xl";

            menuBtn.setAttribute("aria-expanded", "false");

        });

    });

}


// CONTACT FORM

const contactForm = document.getElementById("contactForm");
const contactStatus = document.getElementById("contactStatus");

if (contactForm && contactStatus) {

    contactForm.addEventListener("submit", async function (event) {

        event.preventDefault();

        const button = contactForm.querySelector('button[type="submit"]');

        if (!button) return;

        button.disabled = true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Sending...';

        contactStatus.textContent = "";

        try {

            const response = await fetch("send_message.php", {
                method: "POST",
                body: new FormData(contactForm),
                headers: {
                    "Accept": "application/json"
                }
            });

            let result;

            try {
                result = await response.json();
            } catch (jsonError) {
                throw new Error(
                    "The server returned an invalid response. Check send_message.php."
                );
            }

            if (!response.ok || !result.ok) {

                throw new Error(
                    result.error || "Message could not be sent. Please try again."
                );

            }

            contactStatus.textContent =
                "Your message was submitted successfully!";

            contactStatus.className =
                "text-sm text-center text-emerald-400";

            contactForm.reset();

        } catch (error) {

            contactStatus.textContent =
                error.message || "Please try again.";

            contactStatus.className =
                "text-sm text-center text-red-400";

            console.error("Contact form error:", error);

        } finally {

            button.disabled = false;

            button.innerHTML =
                '<i class="fa-solid fa-paper-plane mr-2"></i> Send Message';

        }

    });

}

</script>

</body>
</html>
