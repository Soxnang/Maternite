<?php
class Utilisateur {
    public int $id;
    public string $nom;
    public string $email;
    public string $password;
    public string $role; // admin, medecin, sage_femme
    public bool $actif;
}
