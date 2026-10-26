<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tadege Yilkal | Full-Stack Software Engineer & CS Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        html { scroll-behavior: smooth; }
        
        /* =========================================
           EXACT RGB GRADIENT BUTTON (From your image)
           ========================================= */
        .btn-rgb {
            /* Green to Yellow to Red */
            background: linear-gradient(90deg, #10b981 0%, #fbbf24 50%, #ef4444 100%);
            background-size: 200% auto;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            cursor: pointer;
        }
        .btn-rgb:hover {
            background-position: right center;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5);
            transform: translateY(-2px);
        }

        .separated-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.8), 0 4px 20px 0 rgba(79, 70, 229, 0.15);
        }
        .header-avatar-ring { box-shadow: 0 0 12px rgba(99, 102, 241, 0.6); }
        
        .nav-link { position: relative; transition: color 0.3s ease; }
        .nav-link::after {
            content: ''; position: absolute; width: 0%; height: 2px; bottom: -4px; left: 0;
            background: linear-gradient(90deg, #6366f1, #a855f7); transition: width 0.3s ease; border-radius: 2px;
        }
        .nav-link:hover::after { width: 100%; }
        
        .glass-card {
            background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            border-color: rgba(16, 185, 129, 0.4);
            transform: translateY(-3px);
        }
        .glow-effect { box-shadow: 0 0 25px -5px rgba(99, 102, 241, 0.3); }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white">

    <header class="fixed top-0 left-0 w-full z-50 separated-header">
        <div class="max-w-7xl mx-auto px-6 py-3.5 flex justify-between items-center">
            <a href="#hero" class="flex items-center space-x-3.5 group">
                <div class="relative">
                    <img src="download.jpg" alt="Tadege Yilkal Profile" class="w-10 h-10 rounded-full object-cover border-2 border-indigo-400/80 header-avatar-ring group-hover:scale-105 transition duration-300">
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div class="flex items-center text-xl font-extrabold tracking-tight">
                    <span class="text-indigo-400 group-hover:text-indigo-300 transition">Tadege</span>
                    <span class="inline-block w-2"></span>
                    <span class="text-slate-100 group-hover:text-white transition">Yilkal</span>
                </div>
            </a>

            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold text-slate-300">
                <a href="#about" class="nav-link hover:text-indigo-300">About</a>
                <a href="#skills" class="nav-link hover:text-indigo-300">Skills</a>
                <a href="#projects" class="nav-link hover:text-indigo-300">Projects</a>
                <a href="#cv" class="nav-link hover:text-indigo-300">CV</a>
                <a href="#contact" class="nav-link hover:text-indigo-300">Contact</a>
            </div>

            <div class="flex items-center space-x-4">
                <!-- RGB Header Button -->
                <a href="#contact" class="hidden sm:flex btn-rgb font-semibold px-5 py-2.5 rounded-xl text-sm transition items-center space-x-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Get in Touch</span>
                </a>
                <button id="menu-btn" class="md:hidden text-slate-300 hover:text-white focus:outline-none p-2" aria-label="Toggle Navigation">
                    <i id="menu-icon" class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-slate-900/95 border-b border-indigo-500/20 px-6 py-4 space-y-3">
            <a href="#about" class="block text-slate-300 hover:text-indigo-400 font-medium py-1">About</a>
            <a href="#skills" class="block text-slate-300 hover:text-indigo-400 font-medium py-1">Skills</a>
            <a href="#projects" class="block text-slate-300 hover:text-indigo-400 font-medium py-1">Projects</a>
            <a href="#cv" class="block text-slate-300 hover:text-indigo-400 font-medium py-1">CV</a>
            <a href="#contact" class="block text-slate-300 hover:text-indigo-400 font-medium py-1">Contact</a>
            <a href="#contact" class="inline-block w-full text-center btn-rgb font-semibold px-4 py-2.5 rounded-xl text-sm mt-2">Get in Touch</a>
        </div>
    </header>

    <section id="hero" class="min-h-screen flex items-center justify-center pt-28 pb-16 px-6 relative overflow-hidden">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-4xl mx-auto text-center relative z-10">
            <div class="mb-8 flex justify-center">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full blur opacity-60 group-hover:opacity-100 transition duration-500"></div>
                    <img src="download.jpg" alt="Tadege Yilkal Hero Image" class="relative w-40 h-40 sm:w-48 sm:h-48 rounded-full object-cover border-2 border-slate-900 shadow-2xl">
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-950/80 border border-indigo-800/60 mb-6 text-indigo-300 text-xs sm:text-sm font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Available for Full-Stack & Engineering Roles
            </div>

            <h1 class="text-4xl sm:text-7xl font-extrabold tracking-tight mb-6 text-slate-100">
                Hi, I'm <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">Tadege Yilkal</span>
            </h1>

            <p class="text-xl text-slate-300 font-medium mb-4">
                Full-Stack Software Engineer & Computer Scientist
            </p>

            <p class="text-base sm:text-lg text-slate-400 mb-10 max-w-2xl mx-auto leading-relaxed">
                Specializing in robust backend systems, responsive user interfaces, database architectures, and data-driven machine learning models.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="#projects" class="btn-rgb font-semibold px-7 py-3.5 rounded-xl transition flex items-center gap-2">
                    <span>Explore My Projects</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </a>
                <a href="#contact" class="bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700 font-semibold px-7 py-3.5 rounded-xl transition flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                    <span>Contact Me</span>
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 px-6 bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-slate-100 mb-4">About Me</h2>
            <p class="text-slate-400 text-center max-w-xl mx-auto mb-12">Passionate about turning complex theoretical concepts into scalable, practical software solutions.</p>

            <div class="glass-card rounded-3xl p-8 sm:p-12 shadow-2xl">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">
                    <div class="md:col-span-4 flex justify-center">
                        <img src="download.jpg" alt="Tadege Yilkal Profile Card" class="w-48 h-48 rounded-2xl object-cover border border-slate-700 shadow-xl glow-effect">
                    </div>
                    <div class="md:col-span-8">
                        <h3 class="text-2xl font-bold text-slate-100 mb-3">Full-Stack Developer & CS Senior</h3>
                        <p class="text-slate-300 leading-relaxed text-base mb-4">
                            I am a 4th-year Computer Science undergraduate student at Injibara University with a comprehensive focus on end-to-end web application development, low-level architecture, and data science.
                        </p>
                        <p class="text-slate-300 leading-relaxed text-base">
                            My technical toolkit bridges front-end design, secure back-end API integration, database administration using MySQL, and predictive modeling in Python. I thrive on solving real-world computing problems through clean architecture, optimized database queries, and modular code design.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center space-x-4 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                        <div class="p-3 bg-indigo-950 rounded-lg text-indigo-400 border border-indigo-800/50">
                            <i class="fa-solid fa-layer-group text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Specialization</p>
                            <p class="font-bold text-slate-200">Full-Stack Development</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                        <div class="p-3 bg-indigo-950 rounded-lg text-indigo-400 border border-indigo-800/50">
                            <i class="fa-solid fa-graduation-cap text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Education</p>
                            <p class="font-bold text-slate-200">B.Sc. Computer Science</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                        <div class="p-3 bg-indigo-950 rounded-lg text-indigo-400 border border-indigo-800/50">
                            <i class="fa-solid fa-code-branch text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Focus</p>
                            <p class="font-bold text-slate-200">Web Systems & ML</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="skills" class="py-24 px-6 border-t border-slate-800/80">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-slate-100 mb-4">Technical Expertise</h2>
            <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">A broad spectrum of technologies covering the full development lifecycle.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="glass-card rounded-2xl p-6 hover:border-indigo-500/50 transition group">
                    <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 mb-6 border border-indigo-800/50 group-hover:scale-110 transition">
                        <i class="fa-solid fa-code text-xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-100 text-lg mb-4">Frontend Development</h3>
                    <ul class="space-y-2.5 text-sm text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>HTML5 & Semantic Markup</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>CSS3 & Tailwind CSS</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>JavaScript (ES6+)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Responsive Web Design</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Interactive UI/UX Form Flow</li>
                    </ul>
                </div>

                <div class="glass-card rounded-2xl p-6 hover:border-indigo-500/50 transition group">
                    <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 mb-6 border border-indigo-800/50 group-hover:scale-110 transition">
                        <i class="fa-solid fa-server text-xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-100 text-lg mb-4">Backend & Databases</h3>
                    <ul class="space-y-2.5 text-sm text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>PHP Scripting</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>MySQL Database Design</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Relational ER Diagrams</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Server Setup (XAMPP/Apache)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>CRUD Operations & Security</li>
                    </ul>
                </div>

                <div class="glass-card rounded-2xl p-6 hover:border-indigo-500/50 transition group">
                    <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 mb-6 border border-indigo-800/50 group-hover:scale-110 transition">
                        <i class="fa-solid fa-brain text-xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-100 text-lg mb-4">Python & Data Science</h3>
                    <ul class="space-y-2.5 text-sm text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Python Programming</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Pandas & Scikit-Learn</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Seaborn & Matplotlib</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>K-Nearest Neighbors (KNN)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Rule-Based Expert Systems</li>
                    </ul>
                </div>

                <div class="glass-card rounded-2xl p-6 hover:border-indigo-500/50 transition group">
                    <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 mb-6 border border-indigo-800/50 group-hover:scale-110 transition">
                        <i class="fa-solid fa-microchip text-xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-100 text-lg mb-4">Core CS & Systems</h3>
                    <ul class="space-y-2.5 text-sm text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Data Structures & Algorithms</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>OS CPU Scheduling & IPC</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>8086 Assembly Language</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>UML Systems Architecture</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-indigo-400"></i>Networking & Security Basics</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="projects" class="py-24 px-6 bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-slate-100 mb-4">Featured Projects</h2>
            <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">Demonstrating practical application in web architecture, backend engineering, and machine learning models.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Project 1: Link goes to tadegesimsinu.onrender.com -->
                <a href="https://tadegesimsinu.onrender.com" target="_blank" rel="noopener noreferrer" class="glass-card rounded-2xl p-8 flex flex-col justify-between hover:border-indigo-500/50 transition group block">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-2xl font-bold text-slate-100 group-hover:text-indigo-400 transition">Student Information Management System</h3>
                            <span class="text-xs bg-indigo-950 text-indigo-300 font-semibold px-3 py-1 rounded-full border border-indigo-800/50">Full-Stack Web</span>
                        </div>
                        <p class="text-slate-300 text-sm mb-6 leading-relaxed">
                            A robust web-based portal developed for managing student academic records. Features secure database authentication, search functionality, profile update forms, dynamic record rendering, and full database CRUD workflows using PHP and MySQL.
                        </p>
                        <ul class="text-xs text-slate-400 space-y-2 mb-6">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Custom relational MySQL database architecture</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Interactive navigation and real-time form validation</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Responsive UI styled with custom CSS & HTML</li>
                        </ul>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-800">
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">PHP</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">MySQL</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">JavaScript</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">HTML5/CSS3</span>
                    </div>
                </a>

                <!-- Project 2 -->
                <div class="glass-card rounded-2xl p-8 flex flex-col justify-between hover:border-indigo-500/50 transition group">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-2xl font-bold text-slate-100 group-hover:text-indigo-400 transition">ML Data Preprocessing & KNN Classifier</h3>
                            <span class="text-xs bg-indigo-950 text-indigo-300 font-semibold px-3 py-1 rounded-full border border-indigo-800/50">Data Science & AI</span>
                        </div>
                        <p class="text-slate-300 text-sm mb-6 leading-relaxed">
                            An end-to-end data analysis pipeline built in Python. Implemented data cleaning, feature scaling, correlation analysis using Seaborn heatmaps, and classification using the K-Nearest Neighbors (KNN) algorithm with comprehensive accuracy evaluation.
                        </p>
                        <ul class="text-xs text-slate-400 space-y-2 mb-6">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Automated data preprocessing and missing value handler</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Exploratory pairplot and heatmap visualizations</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-indigo-400"></i> Precision, recall, and confusion matrix evaluation</li>
                        </ul>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-800">
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">Python</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">Pandas</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">Scikit-Learn</span>
                        <span class="text-xs font-mono text-indigo-300 bg-indigo-950/80 px-3 py-1 rounded-md border border-indigo-900">Seaborn</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cv" class="py-24 px-6 border-t border-slate-800/80">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-slate-100 mb-4">My CV</h2>
            <p class="text-slate-400 text-center max-w-xl mx-auto mb-12">
                Download my résumé, view all uploaded CVs, or upload your own CV for me to review.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="glass-card rounded-2xl p-8 flex flex-col justify-between hover:border-indigo-500/50 transition">
                    <div>
                        <div class="w-14 h-14 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-800/50 mb-5">
                            <i class="fa-solid fa-file-pdf text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-100 mb-2">Download My CV</h3>
                        <p class="text-sm text-slate-400 mb-6">
                            Get a PDF copy of my full résumé — education, skills, and projects.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <a href="Tadege_Yilkal_CV.pdf" download
                           class="w-full btn-rgb font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-download"></i> Download CV (PDF)
                        </a>
                        <a href="view_cv.php" target="_blank"
                           class="w-full bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-folder-open"></i> View All Uploaded CVs
                        </a>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-8 hover:border-indigo-500/50 transition">
                    <div class="w-14 h-14 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-800/50 mb-5">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-100 mb-2">Upload Your CV</h3>
                    <p class="text-sm text-slate-400 mb-5">
                        Send me your CV (PDF, DOC, DOCX — max 5 MB).
                    </p>

                    <form id="cvUploadForm" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Your CV File</label>
                            <input type="file" name="cv" id="cvFile" accept=".pdf,.doc,.docx" required
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition
                                          file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:text-xs hover:file:bg-indigo-500 file:cursor-pointer">
                        </div>
                        <button type="submit"
                                class="w-full btn-rgb font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-upload"></i> Upload CV
                        </button>
                        <div id="uploadStatus" class="text-sm text-center hidden"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-24 px-6 bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-slate-100 mb-4">Get In Touch</h2>
            <p class="text-slate-400 text-center max-w-xl mx-auto mb-16">I am open to full-stack software development roles, projects, and collaborations. Reach out directly!</p>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-5 space-y-6">
                    <a href="tel:0928293370" class="glass-card p-6 rounded-2xl flex items-center space-x-4 hover:border-indigo-500/60 transition group block">
                        <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-800/50 group-hover:scale-110 transition shrink-0">
                            <i class="fa-solid fa-phone text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">Phone</span>
                            <span class="text-slate-200 font-medium">0928293370</span>
                        </div>
                    </a>

                    <a href="https://t.me/tadege123" target="_blank" rel="noopener noreferrer" class="glass-card p-6 rounded-2xl flex items-center space-x-4 hover:border-indigo-500/60 transition group block">
                        <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-800/50 group-hover:scale-110 transition shrink-0">
                            <i class="fa-brands fa-telegram text-xl"></i>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">Telegram</span>
                            <span class="text-slate-200 font-medium">@tadege123</span>
                        </div>
                    </a>

                    <a href="mailto:taadegeyilkal@gmail.com" class="glass-card p-6 rounded-2xl flex items-center space-x-4 hover:border-indigo-500/60 transition group block">
                        <div class="w-12 h-12 bg-indigo-950 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-800/50 group-hover:scale-110 transition shrink-0">
                            <i class="fa-solid fa-envelope text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">Gmail</span>
                            <span class="text-slate-200 font-medium text-sm">taadegeyilkal@gmail.com</span>
                        </div>
                    </a>
                </div>

                <div class="lg:col-span-7 glass-card p-8 rounded-2xl">
                    <h3 class="text-xl font-bold text-slate-100 mb-6">Send Me a Message</h3>
                    <form action="mailto:taadegeyilkal@gmail.com" method="post" enctype="text/plain" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Your Name</label>
                                <input type="text" required placeholder="" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Your Email</label>
                                <input type="email" required placeholder="" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Subject</label>
                            <input type="text" required placeholder="" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Message</label>
                            <textarea rows="4" required placeholder="Write your message here..." class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500"></textarea>
                        </div>
                        <button type="submit" class="w-full btn-rgb text-white font-semibold py-3.5 rounded-xl transition">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-8 border-t border-slate-800/80 text-center text-xs text-slate-500">
        <p>© 2026 Tadege Yilkal. Engineered with modern web standards.</p>
    </footer>

    <script>
        // Mobile Menu Toggle
        const menuBtn =