 const questions = document.querySelectorAll('.faq-question');

     for(const button of questions){
    button.addEventListener('click', () => {
        const answer = button.nextElementSibling;

       if(answer.classList.contains('hidden')){
           answer.classList.remove('hidden')
       }else {
           answer.classList.add('hidden')
       }
    });
}

