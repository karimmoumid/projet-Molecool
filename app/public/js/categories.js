// Sélection de l'élément d'entrée pour le nombre d'équipements
const number = document.querySelector("#category_form_equipments");

// Ajout d'un écouteur d'événement pour l'entrée de l'utilisateur
number.addEventListener("input", function () {
    // Vérification que la valeur n'est pas inférieure à zéro
    if (this.value < 0) {
        this.value = 0; // Réinitialisation à zéro si la valeur est négative
    }

    // Sélection du template pour les équipements
    const template = document.querySelector("#template_categories");

    // Vérification et suppression des équipements existants
    if (this.closest("#category_form").querySelector('.equipement')) {
        const equipements = document.querySelectorAll(".equipement");
        for (let equipment of equipements) {
            equipment.remove(); // Suppression de chaque équipement existant
        }
    }

    // Création de nouveaux équipements en fonction de la valeur entrée
    for (let i = 1; i <= this.value; i++) {
        // Clonage du contenu du template
        const clone = template.content.cloneNode(true);

        // Ajout de la classe 'equipement' à l'élément cloné
        clone.querySelector('div').classList.add('equipement');

        // Sélection et modification du label et de l'input dans le clone
        const label = clone.querySelector('label');
        const input = clone.querySelector('input');

        // Configuration des attributs et du contenu du label
        label.setAttribute("for", `equipement${i}`);
        label.textContent = `Equipement ${i}`;

        // Configuration des attributs de l'input
        input.setAttribute('id', `category_form_equipements_${i - 1}`);
        input.setAttribute('name', `category_form[equipements][${i - 1}]`);

        // Ajout du clone modifié au formulaire
        this.closest('#category_form').appendChild(clone);
    }
});
