import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'results'];

    search() {
        const query = this.inputTarget.value;

        if (query.length < 2) {
            this.resultsTarget.innerHTML = '';
            return;
        }

        fetch(`/predicthq/place/${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            this.resultsTarget.innerHTML = html;
        });
    }
}
