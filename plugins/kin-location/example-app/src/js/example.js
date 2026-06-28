import { KinLocation } from '@kin/location';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinLocation.echo({ value: inputValue })
}
