function toggleContentAndLink(contentElementId, viewType, filePath, toggleLinkId) {
    const contentElement = document.getElementById(contentElementId);
    const toggleLink = document.getElementById(toggleLinkId);

    if (!contentElement || !toggleLink) {
        console.error('Content element or toggle link not found:', contentElementId, toggleLinkId);
        return;
    }

    const isHidden = contentElement.style.display === 'none' || contentElement.style.display === '';

    if (isHidden) {
        contentElement.style.display = 'block';
        toggleLink.textContent = '(Hide)';

        if (viewType === 'image' && contentElement.querySelector('img') === null) {
            contentElement.innerHTML = `<img src="${filePath}" alt="File Content" class="inline-image">`;
        } else if (viewType === 'pdf' && contentElement.querySelector('iframe') === null) {
            contentElement.innerHTML = `<iframe src="${filePath}" class="inline-pdf"></iframe>`;
        }

    } else {
        contentElement.style.display = 'none';
        toggleLink.textContent = '(View)';
    }
}