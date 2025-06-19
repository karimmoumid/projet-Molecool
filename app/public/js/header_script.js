let burger = document.querySelector('header svg');
burger.addEventListener('click', function(){
let list = this.previousElementSibling;
if(list.classList.contains('hidden')){
    list.classList.remove('hidden');
}else{
    list.classList.add('hidden');
}
})