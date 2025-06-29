const date = document.querySelector('#day');
const button = document.querySelector('button');
const patient = document.querySelector('#patient-name')

function showAllAppointments() {
    const appointments = document.querySelectorAll(".appointment article");
    for (let appointment of appointments) {
        appointment.classList.remove('hidden');
        appointment.classList.remove('hidden_patient');
        const items = appointment.querySelectorAll('li[data-name]');
        for (let item of items) {
            item.closest('.box').classList.remove('hidden_patient');
        }
    }
}

date.addEventListener('change', function () {
    const appointments = document.querySelectorAll(".appointment article");
    let date = this.value;
    for (let appointment of appointments) {
        let appointmentDate = appointment.getAttribute('data-id');

        if (appointmentDate === date) {
            appointment.classList.remove('hidden');
        } else {
            appointment.classList.add('hidden');
        }
    }
});
button.addEventListener('click', function (e){
    e.preventDefault();
    showAllAppointments();
    // Réinitialiser les filtres
    date.value = "";
    patient.value = "";
})

patient.addEventListener('change', function (){
    const appointments = document.querySelectorAll(".appointment article");
let name = this.value;
    for (let appointment of appointments){
        const items = appointment.querySelectorAll('li[data-name]');
        let hasVisiblePatient = false;

        for (let item of items){
            if (item.getAttribute('data-name') === name){
                item.closest('.box').classList.remove('hidden_patient');
                hasVisiblePatient = true;
            } else {
                item.closest('.box').classList.add('hidden_patient');
            }
        }

        // Si aucun patient ne correspond dans cet article, on le cache
        if (hasVisiblePatient){
            appointment.classList.remove('hidden_patient');
            appointment.classList.remove('hidden_patient')
        }else{
            appointment.classList.add('hidden_patient');
            appointment.classList.add('hidden_patient')
        }
    }
})
