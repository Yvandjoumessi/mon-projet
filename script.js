const formConnexion = document.getElementById('form-connexion');
const messageErreur = document.getElementById('message-erreur');

formConnexion.addEventListener('sbmit', (e) =>{
    e.preventDefault();
    const login = document.getElementById('login').value;
    const motDePasse = document.getElementById('mot_de_passe').value;

if(login === '' || motDePasse === ''){
    messageErreur.textContent = 'veuillez remplir tous les champs';
    return;
}
fetch('login.php', {
    method: 'post',
    headers: {
        'Content-type' : 'application/json'

    },

    body: JSON.stringify({
        login,
        motDePasse
    })
})

.then((response) => response.json())
.then((data) => {

    if (data.sccess){

        windows.locatio.href = 'login.php';
    }else{
        messsageErreur.textContent=data.message;
    }
})

.catch((error) => {
    console.error(error);
});
});