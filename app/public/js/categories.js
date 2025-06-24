const number = document.querySelector("#category_form_equipments");


number.addEventListener("input", function (){
    if (this.value <0){
        this.value =0;
    }
const template = document.querySelector("#template_categories")
    if (this.closest("#category_form").querySelector('.equipement')){
const equipements = document.querySelectorAll(".equipement");
for(let equipment of equipements){
    equipment.remove();
}
    }
    for (let i=1; i <= this.value; i++){
        const clone = template.content.cloneNode(true);
        clone.querySelector('div').classList.add('equipement');
        const label = clone.querySelector('label');
        const input = clone.querySelector('input');
        label.setAttribute("for",`equipement${i}`);
        label.textContent = `Equipement ${i}`;
        input.setAttribute('id',`category_form_equipements_${i - 1}`);
        input.setAttribute('name',`category_form[equipements][${i - 1}]`);
        this.closest('#category_form').appendChild(clone);
    }


})