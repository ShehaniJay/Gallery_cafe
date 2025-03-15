document.getElementById('reservationForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const name = document.getElementById('name').value;
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const guests = document.getElementById('guests').value;

    if (name && date && time && guests) {
        alert(`Reservation confirmed for ${name} on ${date} at ${time} for ${guests} guests.`);
    } else {
        alert('Please fill in all fields.');
    }
});



// script for logIn page




