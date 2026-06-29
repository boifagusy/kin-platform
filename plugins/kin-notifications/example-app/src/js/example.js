import { KinNotifications } from '@kin/notifications';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinNotifications.echo({ value: inputValue })
}
