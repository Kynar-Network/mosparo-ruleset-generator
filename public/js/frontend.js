/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2024 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
    'use strict'
  
     // Function to switch between steps
     function showStep(stepId) {
        $('.step').addClass('d-none');
        $('#' + stepId).removeClass('d-none');
    }

    // Event listener for choosing to upload a JSON file
    $('#uploadJsonRadio').click(function() {
        $('#jsonFileInputContainer').removeClass('d-none');
        validateJson = true;
    });

    // Event listener for choosing not to upload a JSON file
    $('#createNewJsonRadio').click(function() {
        $('#jsonFileInputContainer').addClass('d-none');
        validateJson = false;
    });

    let validateJson = false;

    // Function to validate the uploaded JSON file format
    function validateUploadedJson(file) {
        const reader = new FileReader();
        return new Promise((resolve, reject) => {
            reader.onload = (event) => {
                try {
                    const jsonData = JSON.parse(event.target.result);
                    if (!jsonData.lastUpdatedAt || !jsonData.refreshInterval || !Array.isArray(jsonData.rules)) {
                        reject('Invalid JSON format.');
                        return;
                    }
                    for (const rule of jsonData.rules) {
                        if (!rule.name || !rule.description || !rule.type || !Array.isArray(rule.items) || typeof rule.spamRatingFactor !== 'number' || !rule.uuid) {
                            reject('Invalid JSON format in rules array.');
                            return;
                        }
                        for (const item of rule.items) {
                            if (!item.uuid || !item.type || !item.value || typeof item.rating !== 'number') {
                                reject('Invalid JSON format in items array.');
                                return;
                            }
                        }
                    }
                    resolve(jsonData);
                } catch (e) {
                    reject('Error parsing JSON file: ' + e.message);
                }
            };
            reader.onerror = () => {
                reject('Error reading the file.');
            };
            reader.readAsText(file);
        });
    }
    
    // Function to populate the settings form with data from the uploaded JSON
    function populateSettings(jsonData) {
        $('#ruleName').val(jsonData.rules[0].name || '');
        $('#description').val(jsonData.rules[0].description || '');
        $('#ruleType').val(jsonData.rules[0].type || '');
        $('#spamRating').val(jsonData.rules[0].spamRatingFactor || '');
        $('#fileName').val(jsonData.fileName || '');
    }

    // Function to log messages
    function logMessage(message, isError = false, isSkipped = false, isChanged = false) {
        const consoleDivs = [
            { id: 'general-console', condition: !isError && !isSkipped && !isChanged },
            { id: 'error-console', condition: isError },
            { id: 'skipped-console', condition: isSkipped },
            { id: 'changed-console', condition: isChanged }
        ];

        for (const { id, condition } of consoleDivs) {
            if (condition) {
                let messageElement = document.createElement('div');
                messageElement.innerHTML = message;
                if (isError) messageElement.classList.add('error');
                if (isSkipped) messageElement.classList.add('skipped');
                if (isChanged) messageElement.classList.add('changed');

                const consoleDiv = document.getElementById(id);
                consoleDiv.appendChild(messageElement);

                // Limit the number of logs to maxLogs
                const logs = consoleDiv.getElementsByTagName('div');
                if (logs.length > maxLogs) {
                    logs[0].remove();
                }

                consoleDiv.scrollTop = consoleDiv.scrollHeight;
                break;
            }
        }
    }


  })()