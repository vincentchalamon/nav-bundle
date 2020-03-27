Feature: Create-Retrieve-Update-Delete
  In order to handle an entity
  As a client software developer
  I need to be able to handle a contact entity through API Platform.

  Scenario: I cannot create a contact
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/contacts" with body:
    """
    {
      "name": "John DOE",
      "email": "john.doe@example.com",
      "phone": "01 23 45 67 89",
      "company": "Example"
    }
    """
    Then the response status code should be 405

  Scenario: Get a contact
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/contacts/CC009894"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Contact",
      "@id": "/contacts/CC009894",
      "@type": "Contact",
      "name": "Pascale Lacarelle",
      "email": "n.grigorova@groupe-hli.com",
      "phone": null,
      "type": "Person",
      "company": "DEKRA INSPECTION"
    }
    """

  Scenario: Get a collection of contacts
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/contacts"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Contact",
      "@id": "/contacts",
      "@type": "hydra:Collection",
      "hydra:member": [
        {
          "@id": "/contacts/CC009894",
          "@type": "Contact",
          "name": "Pascale Lacarelle",
          "email": "n.grigorova@groupe-hli.com",
          "phone": null,
          "type": "Person",
          "company": "DEKRA INSPECTION"
        },
        {
          "@id": "/contacts/CC009896",
          "@type": "Contact",
          "name": "Naïma LASSOUED ",
          "email": null,
          "phone": "1 44 04 43 48  ",
          "type": "Person",
          "company": "ISS HYGIENE ET PREVENTION"
        },
        {
          "@id": "/contacts/CC009899",
          "@type": "Contact",
          "name": "Jean-philippe ROUX",
          "email": null,
          "phone": "4 72 65 52 23",
          "type": "Person",
          "company": "THYSSEN KRUPP"
        },
        {
          "@id": "/contacts/CC009907",
          "@type": "Contact",
          "name": "Audrey Cavallaro ",
          "email": null,
          "phone": "4 96 13 08 14 ",
          "type": "Person",
          "company": "SCHINDLER"
        },
        {
          "@id": "/contacts/CC009910",
          "@type": "Contact",
          "name": "Chantal Thery ",
          "email": null,
          "phone": "3 28 25 92 16",
          "type": "Person",
          "company": "BUREAU VERITAS"
        },
        {
          "@id": "/contacts/CC009915",
          "@type": "Contact",
          "name": "Mylène RAMIREZ",
          "email": null,
          "phone": "1 40 95 97 97",
          "type": "Person",
          "company": "MULTIMAT"
        },
        {
          "@id": "/contacts/CC009919",
          "@type": "Contact",
          "name": "Kevin Arbre",
          "email": null,
          "phone": "1 41 94 53 28",
          "type": "Person",
          "company": "SUDAC"
        },
        {
          "@id": "/contacts/CC009921",
          "@type": "Contact",
          "name": "Stephane CLER",
          "email": null,
          "phone": "6 70 94 40 06",
          "type": "Person",
          "company": "KONE"
        },
        {
          "@id": "/contacts/CC009923",
          "@type": "Contact",
          "name": "Marc LACAZE",
          "email": null,
          "phone": "6 16 95 89 98",
          "type": "Person",
          "company": "SOLVAC"
        },
        {
          "@id": "/contacts/CC009928",
          "@type": "Contact",
          "name": "Eric",
          "email": null,
          "phone": null,
          "type": "Person",
          "company": "RIF EXTINCTEURS"
        },
        {
          "@id": "/contacts/CC009929",
          "@type": "Contact",
          "name": "Sandra AUBEY",
          "email": null,
          "phone": "5 62 18 70 55",
          "type": "Person",
          "company": "DEKRA"
        },
        {
          "@id": "/contacts/CC009933",
          "@type": "Contact",
          "name": "Damien Cottenceau ",
          "email": null,
          "phone": "6 89 94 81 34",
          "type": "Person",
          "company": "ADVENIS PM GRAND OUEST"
        },
        {
          "@id": "/contacts/CC009935",
          "@type": "Contact",
          "name": "Magalie PORTEL",
          "email": null,
          "phone": "1 69 45 03 69",
          "type": "Person",
          "company": "DK"
        },
        {
          "@id": "/contacts/CC009939",
          "@type": "Contact",
          "name": "Service Client",
          "email": null,
          "phone": null,
          "type": "Person",
          "company": "CLIMESPACE"
        },
        {
          "@id": "/contacts/CC009941",
          "@type": "Contact",
          "name": "Service Facturation",
          "email": null,
          "phone": null,
          "type": "Person",
          "company": "CPCU"
        },
        {
          "@id": "/contacts/CC009942",
          "@type": "Contact",
          "name": "Mustapha BOUHOUCH ",
          "email": null,
          "phone": "1 41 78 96 58 ",
          "type": "Person",
          "company": "NSA"
        },
        {
          "@id": "/contacts/CC009943",
          "@type": "Contact",
          "name": "Cindy Termoz ",
          "email": null,
          "phone": "4 37 25 36 17",
          "type": "Person",
          "company": "CHAZAL"
        },
        {
          "@id": "/contacts/CC009944",
          "@type": "Contact",
          "name": "Celine BLEINES",
          "email": null,
          "phone": "1 46 69 77 44",
          "type": "Person",
          "company": "OTIS - AGENCE SURESNES"
        },
        {
          "@id": "/contacts/CC009956",
          "@type": "Contact",
          "name": "BRAGANTI Delphine",
          "email": null,
          "phone": "4 42 08 92 38",
          "type": "Person",
          "company": "ABC ETANCHEITE"
        },
        {
          "@id": "/contacts/CC009966",
          "@type": "Contact",
          "name": "Thierry RAUBER",
          "email": null,
          "phone": "1 30 30 58 26",
          "type": "Person",
          "company": "MSI SECURITE"
        },
        {
          "@id": "/contacts/CC009974",
          "@type": "Contact",
          "name": "MARTIN SOPHIE",
          "email": null,
          "phone": "2 37 33 89 29",
          "type": "Person",
          "company": "COVEA RISKS"
        },
        {
          "@id": "/contacts/CC009976",
          "@type": "Contact",
          "name": "Sophie PLANCHENOT",
          "email": null,
          "phone": "2 38 84 41 44",
          "type": "Person",
          "company": "TUNZINI"
        },
        {
          "@id": "/contacts/CC009989",
          "@type": "Contact",
          "name": "Steve Leriche",
          "email": null,
          "phone": null,
          "type": "Person",
          "company": "VEOLIA EAU"
        },
        {
          "@id": "/contacts/CC009991",
          "@type": "Contact",
          "name": "Eric BERETTA ",
          "email": "pp.beretta@test.fr",
          "phone": "3 80 30 39 38",
          "type": "Person",
          "company": "CITYA GESSY-VERNE"
        },
        {
          "@id": "/contacts/CC009992",
          "@type": "Contact",
          "name": "Michel PESCHEUX",
          "email": null,
          "phone": "1 61 37 00 10",
          "type": "Person",
          "company": "GENIEZ IMMOBILIER"
        },
        {
          "@id": "/contacts/CC009995",
          "@type": "Contact",
          "name": "A.HAVEZ",
          "email": null,
          "phone": null,
          "type": "Person",
          "company": "BIOMULTINET"
        },
        {
          "@id": "/contacts/CC009997",
          "@type": "Contact",
          "name": "Service Client",
          "email": null,
          "phone": "0810  333 433",
          "type": "Person",
          "company": "EDF ENTREPRISES GRANDS CLIENTS"
        },
        {
          "@id": "/contacts/CC010025",
          "@type": "Contact",
          "name": "Sophie Delmotte",
          "email": null,
          "phone": "3 28 416 426 ",
          "type": "Person",
          "company": "LST LEBOULANGER SECURITE"
        },
        {
          "@id": "/contacts/CC010029",
          "@type": "Contact",
          "name": "Bartos Jean-François",
          "email": null,
          "phone": "3 20 89 35 35 ",
          "type": "Person",
          "company": "ID VERDE"
        },
        {
          "@id": "/contacts/CC010035",
          "@type": "Contact",
          "name": "Mohamed Ouali",
          "email": null,
          "phone": "6 25 18 53 61",
          "type": "Person",
          "company": "ENERGILEC"
        }
      ],
      "hydra:totalItems": 30,
      "hydra:search": {
        "@type": "hydra:IriTemplate",
        "hydra:template": "/contacts{?no,no[],key,key[],name,name[],email,email[],phone,phone[],type,type[],company,company[]}",
        "hydra:variableRepresentation": "BasicRepresentation",
        "hydra:mapping": [
          {
            "@type": "IriTemplateMapping",
            "variable": "no",
            "property": "no",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "no[]",
            "property": "no",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "key",
            "property": "key",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "key[]",
            "property": "key",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "name",
            "property": "name",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "name[]",
            "property": "name",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "email",
            "property": "email",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "email[]",
            "property": "email",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "phone",
            "property": "phone",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "phone[]",
            "property": "phone",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "type",
            "property": "type",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "type[]",
            "property": "type",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "company",
            "property": "company",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "company[]",
            "property": "company",
            "required": false
          }
        ]
      }
    }
    """

  Scenario: Update a contact
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/contacts/CC009894" with body:
    """
    {
      "@id": "/contacts/CC009894",
      "name": "Jane DOE",
      "email": "jane.doe@example.com"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the header "Content-Location" should be equal to "/contacts/CC009894"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Contact",
      "@id": "/contacts/CC009894",
      "@type": "Contact",
      "name": "Jane DOE",
      "email": "jane.doe@example.com",
      "phone": null,
      "type": "Person",
      "company": "DEKRA INSPECTION"
    }
    """

  Scenario: I cannot delete a contact
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/contacts/CC009894"
    Then the response status code should be 405
