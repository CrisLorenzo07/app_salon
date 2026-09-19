let currentStep = 1;

const appointment = {
  name: "",
  date: "",
  time: "",
  services: [],
};

document.addEventListener("DOMContentLoaded", function () {
  initApp();
});

function initApp() {
  showSection();
  initTabs();
  updatePaginationButtons();
  initNextButton();
  initPreviousButton();

  fetchServices();

  customerName();
  selectedDate();
  selectedTime();
}

function showSection() {
  const previousSection = document.querySelector(".is-visible");
  if (previousSection) {
    previousSection.classList.remove("is-visible");
  }

  const section = document.querySelector(`#step-${currentStep}`);
  section.classList.add("is-visible");

  const previousTab = document.querySelector(".tabs button.is-active");
  if (previousTab) {
    previousTab.classList.remove("is-active");
  }

  const tab = document.querySelector(
    `.tabs button[data-step="${currentStep}"]`,
  );
  tab.classList.add("is-active");
}

function initTabs() {
  const buttons = document.querySelectorAll(".tabs button");

  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      currentStep = parseInt(e.target.dataset.step);
      showSection();
      updatePaginationButtons();
    });
  });
}

function updatePaginationButtons() {
  const previousButton = document.querySelector("#previous");
  const nextButton = document.querySelector("#next");

  if (currentStep === 1) {
    previousButton.classList.add("is-hidden");
    nextButton.classList.remove("is-hidden");
  } else if (currentStep === 3) {
    previousButton.classList.remove("is-hidden");
    nextButton.classList.add("is-hidden");
  } else {
    previousButton.classList.remove("is-hidden");
    nextButton.classList.remove("is-hidden");
  }
}

function initPreviousButton() {
  const previousButton = document.querySelector("#previous");
  previousButton.addEventListener("click", function () {
    if (currentStep <= 1) return;
    currentStep--;
    showSection();
    updatePaginationButtons();
  });
}

function initNextButton() {
  const nextButton = document.querySelector("#next");
  nextButton.addEventListener("click", function () {
    if (currentStep >= 3) return;
    currentStep++;
    showSection();
    updatePaginationButtons();
  });
}

async function fetchServices() {
  try {
    const url = "/api/services";
    const response = await fetch(url);
    const services = await response.json();
    renderServices(services);
  } catch (error) {
    console.log(error);
  }
}

function renderServices(services) {
  services.forEach((service) => {
    const { id, name, price } = service;

    const serviceName = document.createElement("P");
    serviceName.classList.add("service-name");
    serviceName.textContent = name;

    const servicePrice = document.createElement("P");
    servicePrice.classList.add("service-price");
    servicePrice.textContent = `$${price.toFixed(2)}`;

    const serviceElement = document.createElement("DIV");
    serviceElement.classList.add("service");
    serviceElement.dataset.serviceId = id;
    serviceElement.onclick = function () {
      selectService(service);
    };

    serviceElement.appendChild(serviceName);
    serviceElement.appendChild(servicePrice);

    document.querySelector("#services").appendChild(serviceElement);
  });
}

function selectService(service) {
  const { id } = service;
  const { services } = appointment;

  const serviceElement = document.querySelector(`[data-service-id="${id}"]`);

  if (services.some((addedService) => addedService.id === id)) {
    appointment.services = services.filter(
      (addedService) => addedService.id !== id,
    );
    serviceElement?.classList.remove("selected");
  } else {
    services.push(service);
    serviceElement?.classList.add("selected");
  }
  console.log(appointment);
}

function customerName() {
  appointment.name = document.querySelector("#name").value;
}

function selectedDate() {
  const inputDate = document.querySelector("#date");
  inputDate.addEventListener("input", function (e) {
    const day = new Date(e.target.value).getUTCDay();
    if ([6, 0].includes(day)) {
      e.target.value = "";
      showAlert("Fin de semanas se encuetra cerrado", "error");
    } else {
      appointment.date = e.target.value;
    }
  });
}

function selectedTime() {
  const inputTime = document.querySelector("#time");
  inputTime.addEventListener("input", function (e) {
    console.log(e.target.value);

    const appointmentTime = e.target.value;
    const time = appointmentTime.split(":")[0];
    if (time < 9 || time > 19) {
      e.target.value = "";
      showAlert("Hora no Válida", "error");
    } else {
      appointment.time = e.target.value;
    }
  });
}

function showAlert(message, type) {
  const previewAlert = document.querySelector(".alert");
  if (previewAlert) return;

  const alert = document.createElement("DIV");
  alert.textContent = message;
  alert.classList.add("alert");
  alert.classList.add(type);

  const form = document.querySelector("form");
  form.appendChild(alert);

  setTimeout(() => {
    alert.remove();
  }, 3000);
}
