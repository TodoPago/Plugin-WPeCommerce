<style>
    .tp-button:hover {
        color: #e3f400
    }

    .tp-button {
        text-align: right;
        background-color: #E6007E;
        color: white;
        border-radius: 3px;
        padding: 5px;
    }

    .tp-modal {
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        display: block;
        background-color: rgba(10, 10, 10, 0.8);
        z-index: 20;
    }

    .tp-modal:target{
        display: none;
    }

    .get-status-content {
        text-align: center;
        background-color: white;
        width: 600px;
        height: 200px;
        margin: auto;
        color: black;
        z-index: 2010;
        padding: 10px;
        border: 1px red solid;
        border-radius: 5px;
        margin-top: 100px;
    }

    h2 {
        color: black
    }

    .separador {
        margin-bottom: 10px;
        border-color: dimgray dimgray dimgray dimgray;
        border-style: solid;
        border-width: 0 0 1px 0;
        height: 1px;
        background-color: dimgray;
    }

    .tp-close {
        right: 30%;
        text-align: right;
        color: black;
        cursor: pointer;
        position: absolute;
    }

</style>