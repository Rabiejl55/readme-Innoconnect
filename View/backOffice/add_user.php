<form method="POST" action="../Controller/utilisateurC.php"> 
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <select name="type">
        <option value="administrateur">Administrator</option>
        <option value="investisseur">Investor</option>
        <option value="innovateur">Innovator</option>
    </select>
    <input type="date" name="date_inscription" placeholder="date_inscription" required>
    <button type="submit" name="add">Add</button>
</form>
