import { Controller } from '@hotwired/stimulus';
import Modal from 'bootstrap/js/dist/modal'
import axios from "axios";

export default class extends Controller {
    static targets = ['modal', 'modaltitle', 'modalbody'];

    openModal(event) {
        this.modalbodyTarget.innerHTML = "Loading..."
        // this.modaltitleTarget.innerHTML = event.currentTarget.dataset.title
        axios.get(event.currentTarget.dataset.url).then(resp => {
            var out = "";
            if (typeof resp.data === "object") {
                out = JSON.stringify(resp.data);
            } else {
                out = resp.data
            }
            this.modalbodyTarget.innerHTML = `<pre>${out}</pre>`
        }).catch(reason => {
            this.modalbodyTarget.innerHTML = `Failed to fetch log data`
        });

        const modal = new Modal(this.modalTarget);
        modal.show();
    }
}
