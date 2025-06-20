-- Créer la table admin si elle n'existe pas
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insérer un admin par défaut (mot de passe: admin123)
INSERT INTO admin (name, email, password) VALUES 
('Administrateur', 'admin@sportsfield.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE name = name;

-- Créer la table creneau_horaire si elle n'existe pas
CREATE TABLE IF NOT EXISTS creneau_horaire (
    creneau_id INT AUTO_INCREMENT PRIMARY KEY,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL
);

-- Insérer les créneaux horaires par défaut
INSERT INTO creneau_horaire (heure_debut, heure_fin) VALUES 
('08:00:00', '09:00:00'),
('09:00:00', '10:00:00'),
('10:00:00', '11:00:00'),
('11:00:00', '12:00:00'),
('12:00:00', '13:00:00'),
('13:00:00', '14:00:00'),
('14:00:00', '15:00:00'),
('15:00:00', '16:00:00'),
('16:00:00', '17:00:00'),
('17:00:00', '18:00:00'),
('18:00:00', '19:00:00'),
('19:00:00', '20:00:00'),
('20:00:00', '21:00:00'),
('21:00:00', '22:00:00')
ON DUPLICATE KEY UPDATE heure_debut = heure_debut;