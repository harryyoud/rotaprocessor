import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import axios from "axios";

export default class extends Controller {
    static targets = ['modal', 'modaltitle', 'modalbody'];

    openModal(event) {
        this.modalbodyTarget.innerHTML = "Loading..."
        // this.modaltitleTarget.innerHTML = event.currentTarget.dataset.title
        axios.get(event.currentTarget.dataset.url).then(resp => {
            this.modalbodyTarget.innerHTML = `<pre>${JSON.stringify(resp.data, null, 2)}</pre>`
        }).catch(reason => {
            this.modalbodyTarget.innerHTML = `Failed to fetch log data`
        });

        const modal = new Modal(this.modalTarget);
        modal.show();
    }
}
