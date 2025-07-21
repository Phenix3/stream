<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SELDJAM PRODUCTION - Studio de production audiovisuelle au Cameroun, spécialisé dans les films, documentaires et contenus culturels">

        <title>SELDJAM PRODUCTION - Studio de Production Audiovisuelle au Cameroun</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .gold-gradient {
                background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
            }
            
            .text-white {
                color: #FFD700;
            }
            
            .gold-text {
                background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .hero-gradient {
                background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
            }
            
            .glass-effect {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.9);
                border: 1px solid rgba(255, 215, 0, 0.3);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }
            
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            
            .animate-pulse-slow {
                animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            
            .star-logo {
                position: relative;
                display: inline-block;
            }
            
            .star-logo::before {
                content: "★";
                position: absolute;
                top: -5px;
                left: -5px;
                font-size: 1.2em;
                color: #FFD700;
                z-index: -1;
            }
        </style>
    </head>
    <body class="antialiased font-sans bg-white text-gray-900 overflow-x-hidden">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 glass-effect">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                                <div class="text-white text-2xl font-bold">★</div>
                            </div>
                        </div>
                        <div>
                            <span class="text-xl font-bold gold-text">SELDJAM</span>
                            <div class="text-sm text-gray-600 font-medium">PRODUCTION</div>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#services" class="hover:text-yellow-600 transition-colors font-medium">Services</a>
                        <a href="#portfolio" class="hover:text-yellow-600 transition-colors font-medium">Portfolio</a>
                        <a href="#about" class="hover:text-yellow-600 transition-colors font-medium">À propos</a>
                        <a href="#contact" class="hover:text-yellow-600 transition-colors font-medium">Contact</a>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-full hover:from-yellow-600 hover:to-orange-600 transition-all shadow-lg">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-full hover:from-yellow-600 hover:to-orange-600 transition-all shadow-lg">Connexion</a>
                        @endauth
                    </div>
                    
                    <button class="md:hidden text-2xl">☰</button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative min-h-screen flex items-center justify-center hero-gradient">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute top-20 left-10 w-20 h-20 bg-yellow-400/30 rounded-full animate-float"></div>
            <div class="absolute top-40 right-20 w-32 h-32 bg-orange-400/30 rounded-full animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-20 left-1/4 w-16 h-16 bg-yellow-500/30 rounded-full animate-float" style="animation-delay: 4s;"></div>
            
            <div class="relative z-10 text-center max-w-6xl mx-auto px-6">
                <div class="mb-8">
                    <div class="inline-block mb-6">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-2xl mx-auto">
                            <div class="text-4xl text-yellow-500">★</div>
                        </div>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-bold mb-8 leading-tight">
                        <span class="text-white">SELDJAM</span><br>
                        <span class="gold-text">PRODUCTION</span>
                    </h1>
                </div>
                <h2 class="text-3xl md:text-5xl font-bold mb-8 text-white">
                    <span class="gold-text">Créons</span> ensemble des<br>
                    <span class="text-white">histoires</span> qui inspirent
                </h2>
                <p class="text-xl md:text-2xl mb-12 text-white/90 max-w-4xl mx-auto">
                    Studio de production audiovisuelle au Cameroun, spécialisé dans la création de films, 
                    documentaires et contenus culturels qui valorisent notre patrimoine africain.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="#contact" class="px-8 py-4 bg-white text-yellow-600 rounded-full hover:bg-gray-50 transition-all text-lg font-semibold group shadow-lg">
                        Démarrer un projet
                        <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
                    </a>
                    <a href="#portfolio" class="px-8 py-4 border-2 border-white text-white rounded-full hover:bg-white hover:text-yellow-600 transition-all text-lg font-semibold">
                        Voir nos réalisations
                    </a>
                </div>
            </div>
            
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-bold mb-6">
                        Nos <span class="gold-text">Services</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        De la conception à la diffusion, nous accompagnons vos projets audiovisuels 
                        avec expertise et créativité, en mettant en valeur la richesse culturelle africaine.
                    </p>
                </div>
                
                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="group p-8 rounded-2xl bg-white hover:shadow-2xl transition-all duration-500 border border-gray-200 hover:border-yellow-400">
                        <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-.553.894L16 18l-4.447-2.276a1 1 0 00-.894 0L6.447 18l-4.553-2.276a1 1 0 011 14.882V8.618a1 1 0 01.553-.894L6 6l4.447-2.276a1 1 0 01.894 0L16 6l-1 4z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Production Vidéo</h3>
                        <p class="text-gray-600 mb-6">
                            Films institutionnels, documentaires culturels, spots publicitaires, clips musicaux 
                            et contenus web de haute qualité avec une approche narrative unique.
                        </p>
                        <ul class="space-y-2 text-gray-700">
                            <li>• Films institutionnels & corporate</li>
                            <li>• Documentaires culturels</li>
                            <li>• Publicités & spots TV</li>
                            <li>• Clips musicaux africains</li>
                        </ul>
                    </div>

                    <div class="group p-8 rounded-2xl bg-white hover:shadow-2xl transition-all duration-500 border border-gray-200 hover:border-yellow-400">
                        <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Production Audio</h3>
                        <p class="text-gray-600 mb-6">
                            Enregistrement, mixage et mastering professionnel pour tous vos projets 
                            musicaux et sonores avec des équipements de pointe.
                        </p>
                        <ul class="space-y-2 text-gray-700">
                            <li>• Enregistrement studio</li>
                            <li>• Mixage & mastering</li>
                            <li>• Sound design</li>
                            <li>• Podcasts & interviews</li>
                        </ul>
                    </div>

                    <div class="group p-8 rounded-2xl bg-white hover:shadow-2xl transition-all duration-500 border border-gray-200 hover:border-yellow-400">
                        <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Post-Production</h3>
                        <p class="text-gray-600 mb-6">
                            Montage, effets visuels, color grading et finalisation pour donner 
                            vie à vos projets avec une qualité cinématographique.
                        </p>
                        <ul class="space-y-2 text-gray-700">
                            <li>• Montage vidéo</li>
                            <li>• Effets visuels (VFX)</li>
                            <li>• Color grading</li>
                            <li>• Motion design</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Portfolio Section -->
        <section id="portfolio" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-bold mb-6">
                        Nos <span class="gold-text">Réalisations</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Découvrez quelques-unes de nos productions qui ont marqué les esprits 
                        et raconté des histoires uniques du Cameroun et de l'Afrique.
                    </p>
                </div>
                
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-2xl transition-all">
                        <div class="aspect-video bg-gradient-to-br from-yellow-400/20 to-orange-400/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2">"Les Traditions du Nord Cameroun"</h3>
                            <p class="text-gray-600 mb-4">Documentaire sur le patrimoine culturel peul</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-yellow-600 font-semibold">Documentaire</span>
                                <span class="text-sm text-gray-500">2024</span>
                            </div>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <div class="text-white">
                                <p class="text-sm">Une exploration immersive des traditions ancestrales peules</p>
                            </div>
                        </div>
                    </div>

                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-2xl transition-all">
                        <div class="aspect-video bg-gradient-to-br from-yellow-400/20 to-orange-400/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2">"Rythmes du Cameroun"</h3>
                            <p class="text-gray-600 mb-4">Série musicale sur les artistes urbains camerounais</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-yellow-600 font-semibold">Série TV</span>
                                <span class="text-sm text-gray-500">2023</span>
                            </div>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <div class="text-white">
                                <p class="text-sm">Portrait intime des nouvelles voix de la musique urbaine camerounaise</p>
                            </div>
                        </div>
                    </div>

                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-2xl transition-all">
                        <div class="aspect-video bg-gradient-to-br from-yellow-400/20 to-orange-400/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zM6 10h.01M9 10h.01M12 10h.01M15 10h.01M18 10h.01M7 14h.01M10 14h.01M13 14h.01M16 14h.01M19 14h.01" />
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2">"Saveurs d'Afrique Centrale"</h3>
                            <p class="text-gray-600 mb-4">Série culinaire sur la gastronomie camerounaise</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-yellow-600 font-semibold">Série Web</span>
                                <span class="text-sm text-gray-500">2024</span>
                            </div>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <div class="text-white">
                                <p class="text-sm">Un voyage gustatif à travers les saveurs du Cameroun</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-12">
                    <a href="#contact" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full hover:from-yellow-600 hover:to-orange-600 transition-all text-lg font-semibold text-white shadow-lg">
                        Voir plus de projets
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-4xl md:text-6xl font-bold mb-8">
                            À propos de <span class="gold-text">SELDJAM</span>
                        </h2>
                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            Fondé au Cameroun par des passionnés d'audiovisuel, SELDJAM PRODUCTION combine créativité, 
                            innovation technique et expertise narrative pour créer des contenus qui 
                            marquent les esprits et valorisent la richesse culturelle africaine.
                        </p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2">Équipements de Pointe</h3>
                                    <p class="text-gray-600">Caméras 4K, drones, éclairage professionnel et studio d'enregistrement équipé au cœur de Yaoundé.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2">Équipe Expérimentée</h3>
                                    <p class="text-gray-600">Réalisateurs, cadreurs, monteurs et techniciens avec plus de 10 ans d'expérience dans l'industrie audiovisuelle camerounaise.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2">Valorisation Culturelle</h3>
                                    <p class="text-gray-600">Engagement pour la promotion des talents africains et la transmission du patrimoine culturel à travers des œuvres originales.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="aspect-square bg-gradient-to-br from-yellow-400/20 to-orange-400/20 rounded-3xl flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-6xl font-bold gold-text mb-4">100+</div>
                                <div class="text-xl text-gray-700">Projets réalisés</div>
                                <div class="text-md text-gray-500 mt-2">Clients : artistes, institutions, entreprises, ONG</div>
                            </div>
                        </div>
                        <div class="absolute -top-4 -right-4 w-32 h-32 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full opacity-20 animate-pulse-slow"></div>
                        <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full opacity-20 animate-pulse-slow" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-bold mb-6">
                        Contactez <span class="gold-text">SELDJAM PRODUCTION</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Prêt à donner vie à votre projet ? Notre équipe vous accompagne de l'idée à la diffusion. Basés à Yaoundé, nous intervenons partout au Cameroun et en Afrique centrale.
                    </p>
                </div>
                <div class="grid lg:grid-cols-2 gap-16">
                    <div class="space-y-8">
                        <div class="p-8 rounded-2xl bg-white border border-yellow-200 shadow-lg">
                            <h3 class="text-2xl font-bold mb-4 gold-text">Informations de Contact</h3>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center">
                                        <img src="{{ asset('assets/imgs/logo.jpg') }}" alt="Logo Seldjam Production" class="w-8 h-8 object-contain" />
                                    </div>
                                    <div>
                                        <p class="font-semibold">SELDJAM PRODUCTION</p>
                                        <p class="text-gray-600">Maroua, Cameroun</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Email</p>
                                        <p class="text-gray-600">contact@seldjam.com</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Téléphone</p>
                                        <p class="text-gray-600">+237 6XX XXX XXX</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 rounded-2xl bg-white border border-yellow-200 shadow-lg">
                            <h3 class="text-2xl font-bold mb-4 gold-text">Horaires</h3>
                            <div class="space-y-2 text-gray-600">
                                <div class="flex justify-between">
                                    <span>Lundi - Vendredi</span>
                                    <span>9h00 - 18h00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Samedi</span>
                                    <span>10h00 - 16h00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Dimanche</span>
                                    <span>Fermé</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-8 rounded-2xl bg-white border border-yellow-200 shadow-lg">
                        <h3 class="text-2xl font-bold mb-6 gold-text">Envoyez-nous un message</h3>
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold mb-2" for="nom">Nom complet</label>
                                    <input type="text" id="nom" name="nom" class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-yellow-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Votre nom">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-2" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-yellow-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="votre@email.com">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2" for="sujet">Sujet</label>
                                <input type="text" id="sujet" name="sujet" class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-yellow-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Type de projet">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2" for="message">Message</label>
                                <textarea id="message" name="message" rows="5" class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-yellow-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent resize-none" placeholder="Décrivez votre projet..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl hover:from-yellow-600 hover:to-orange-600 transition-all text-lg font-semibold text-white shadow-lg group">
                                Envoyer le message
                                <svg class="inline-block ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-yellow-200">
            <div class="max-w-7xl mx-auto px-6 py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="space-y-6">
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('assets/imgs/logo.jpg') }}" alt="Logo Seldjam Production" class="w-12 h-12 object-contain rounded-lg shadow-lg" />
                            <span class="text-xl font-bold gold-text">SELDJAM PRODUCTION</span>
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            Studio de production audiovisuelle basé à Yaoundé, Cameroun. Nous valorisons la culture africaine à travers des films, documentaires, clips et captations d'événements.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center hover:bg-yellow-400 transition-colors">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center hover:bg-yellow-400 transition-colors">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center hover:bg-yellow-400 transition-colors">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold">Services</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Production Vidéo</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Production Audio</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Post-Production</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Motion Design</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Sound Design</a></li>
                        </ul>
                    </div>
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold">Portfolio</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Films Institutionnels</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Documentaires</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Publicités</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Clips Musicaux</a></li>
                            <li><a href="#" class="hover:text-yellow-600 transition-colors">Séries Web</a></li>
                        </ul>
                    </div>
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold">Contact</h3>
                        <div class="space-y-3 text-gray-600">
                            <p>Yaoundé, Cameroun</p>
                            <p>contact@seldjam.com</p>
                            <p>+237 6XX XXX XXX</p>
                        </div>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-yellow-200">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-500 text-sm">
                            &copy; 2024 SELDJAM PRODUCTION. Tous droits réservés.
                        </p>
                        <div class="flex space-x-6 mt-4 md:mt-0">
                            <a href="#" class="text-gray-500 hover:text-yellow-600 text-sm transition-colors">Mentions légales</a>
                            <a href="#" class="text-gray-500 hover:text-yellow-600 text-sm transition-colors">Politique de confidentialité</a>
                            <a href="#" class="text-gray-500 hover:text-yellow-600 text-sm transition-colors">CGV</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>